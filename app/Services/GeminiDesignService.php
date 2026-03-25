<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Generate design JSON via Gemini API for use in controller or queue jobs.
 */
class GeminiDesignService
{
    /**
     * Generate design and return fabric data plus token usage for billing.
     *
     * @return array{design: array, usage: array{input_tokens: int, output_tokens: int}}
     */
    public function generate(string $prompt, int $width, int $height, ?string $apiKey = null): array
    {
        $apiKey = $apiKey ?: Setting::get('gemini_api_key') ?: config('services.gemini.api_key') ?: env('GEMINI_API_KEY');
        if (! $apiKey) {
            throw new \RuntimeException('Gemini API key is not configured.');
        }

        $systemPrompt = $this->buildGeminiDesignPrompt($width, $height);
        $fullPrompt = 'Create an amazing design for: '.$prompt."\n\n".$systemPrompt;

        $model = Setting::get('gemini_model') ?: env('GEMINI_MODEL', 'gemini-2.5-flash');
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/'.$model.':generateContent?key='.urlencode($apiKey);
        $timeout = min(120, (int) (env('GEMINI_REQUEST_TIMEOUT') ?: 90));
        $response = Http::timeout($timeout)
            ->post($url, [
                'contents' => [
                    ['parts' => [['text' => $fullPrompt]]],
                ],
                'generationConfig' => [
                    'temperature' => 0.75,
                    'maxOutputTokens' => 16384,
                    'responseMimeType' => 'application/json',
                ],
            ]);

        if (! $response->successful()) {
            $body = $response->json();
            $msg = $body['error']['message'] ?? $response->body();
            throw new \RuntimeException('Gemini API error: '.$msg);
        }

        $data = $response->json();
        $text = $this->extractCandidateText($data);
        if ($text === null || $text === '') {
            throw new \RuntimeException('Invalid or empty response from Gemini.');
        }

        $usage = $this->extractUsageFromResponse($data);

        $designData = $this->extractJsonFromAiResponse($text);
        if ($designData === null) {
            throw new \RuntimeException('Failed to parse AI response as JSON. The model may have returned prose, truncated output, or invalid characters in text fields—try again or simplify the prompt.');
        }

        $design = (new AIDocumentGenerator)->convertDesignDataToFabric($designData, $width, $height);

        return ['design' => $design, 'usage' => $usage];
    }

    /**
     * Generate one Fabric canvas per page. Each request asks the model for a single page of a multi-page document.
     *
     * @return array{pages: list<array>, usage: array{input_tokens: int, output_tokens: int}}
     */
    public function generateMultiPage(string $prompt, int $width, int $height, int $pageCount, ?string $apiKey = null): array
    {
        $pageCount = max(1, min(20, $pageCount));
        $fabricPages = [];
        $totalInput = 0;
        $totalOutput = 0;

        for ($i = 1; $i <= $pageCount; $i++) {
            $pageSuffix = $pageCount > 1
                ? "\n\nIMPORTANT: You are designing PAGE {$i} OF {$pageCount} ONLY. Output ONE canvas JSON object for this single page. The multi-page document has {$pageCount} pages total—this response must contain layout and content for page {$i} only (e.g. cover vs inner pages vs closing)."
                : '';
            $result = $this->generate($prompt.$pageSuffix, $width, $height, $apiKey);
            $fabricPages[] = $result['design'];
            $u = $result['usage'] ?? ['input_tokens' => 0, 'output_tokens' => 0];
            $totalInput += (int) ($u['input_tokens'] ?? 0);
            $totalOutput += (int) ($u['output_tokens'] ?? 0);
        }

        return [
            'pages' => $fabricPages,
            'usage' => [
                'input_tokens' => $totalInput,
                'output_tokens' => $totalOutput,
            ],
        ];
    }

    /**
     * Extract token usage from Gemini generateContent response.
     *
     * @return array{input_tokens: int, output_tokens: int}
     */
    /**
     * Concatenate all text parts from the first candidate; handle blocked / empty replies.
     */
    protected function extractCandidateText(array $data): ?string
    {
        $candidates = $data['candidates'] ?? [];
        if ($candidates === []) {
            return null;
        }
        $first = $candidates[0];
        $finishReason = $first['finishReason'] ?? null;
        if ($finishReason === 'MAX_TOKENS') {
            Log::warning('GeminiDesignService: candidate hit MAX_TOKENS (output may be truncated JSON).');
        }
        if ($finishReason === 'SAFETY' || $finishReason === 'RECITATION') {
            Log::warning('GeminiDesignService: candidate blocked', ['finishReason' => $finishReason]);
        }
        $parts = $first['content']['parts'] ?? [];
        $chunks = [];
        foreach ($parts as $part) {
            if (isset($part['text']) && is_string($part['text'])) {
                $chunks[] = $part['text'];
            }
        }

        return $chunks === [] ? null : implode('', $chunks);
    }

    protected function extractUsageFromResponse(array $data): array
    {
        $meta = $data['usageMetadata'] ?? [];
        $input = (int) ($meta['promptTokenCount'] ?? 0);
        $output = (int) ($meta['candidatesTokenCount'] ?? $meta['totalTokenCount'] ?? 0);
        if ($output === 0 && isset($meta['totalTokenCount'])) {
            $output = max(0, (int) $meta['totalTokenCount'] - $input);
        }

        return ['input_tokens' => $input, 'output_tokens' => $output];
    }

    protected function buildGeminiDesignPrompt(int $width, int $height): string
    {
        return <<<PROMPT
You are an expert graphic designer. Return ONLY valid JSON with this exact structure. No markdown, no code blocks, no explanations—just the raw JSON object. Use valid JSON only—no doubled quotes in keys.
{
  "backgroundColor": "#hexcolor",
  "objects": [
    {
      "type": "rect|circle|ellipse|triangle|textbox|image",
      "left": number,
      "top": number,
      "width": number,
      "height": number,
      "radius": number (for circle),
      "rx": number, "ry": number (for ellipse),
      "fill": "#hex only (no CSS gradient strings in fill)",
      "stroke": "#hex only",
      "strokeWidth": number,
      "text": "string" (for textbox),
      "fontSize": number,
      "fontFamily": "Arial",
      "fontWeight": "normal|bold",
      "textAlign": "left|center|right",
      "src": "https://placehold.co/400x200/1e3a5f/ffffff?text=Image" (for image)
    }
  ]
}
Canvas: {$width}x{$height}px. Use professional colors, gradients, shadows, typography. Create a stunning design.
PROMPT;
    }

    protected function extractJsonFromAiResponse(string $text): ?array
    {
        $text = trim($text);
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $text, $m)) {
            $text = trim($m[1]);
        }
        $start = strpos($text, '{');
        if ($start !== false) {
            $depth = 0;
            $inString = false;
            $escape = false;
            $quote = '"';
            $end = -1;
            for ($i = $start; $i < strlen($text); $i++) {
                $c = $text[$i];
                if ($escape) {
                    $escape = false;

                    continue;
                }
                if ($inString) {
                    if ($c === '\\') {
                        $escape = true;
                    } elseif ($c === $quote) {
                        $inString = false;
                    }

                    continue;
                }
                if ($c === '"') {
                    $inString = true;
                    $quote = $c;

                    continue;
                }
                if ($c === '{') {
                    $depth++;
                } elseif ($c === '}') {
                    $depth--;
                    if ($depth === 0) {
                        $end = $i;
                        break;
                    }
                }
            }
            if ($end >= 0) {
                $text = substr($text, $start, $end - $start + 1);
            }
        }
        $text = preg_replace('/""([a-zA-Z_][a-zA-Z0-9_]*)""/', '"$1"', $text);
        $text = preg_replace('/,\s*([}\]])/u', '$1', $text);
        // Models often put raw newlines/tabs inside "text" values; strict JSON forbids that.
        $text = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $text);
        $decodeFlags = JSON_INVALID_UTF8_SUBSTITUTE;
        if (defined('JSON_BIGINT_AS_STRING')) {
            $decodeFlags |= JSON_BIGINT_AS_STRING;
        }
        $decoded = json_decode($text, true, 512, $decodeFlags);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }
        $err = json_last_error_msg();
        $trimmed = rtrim($text);
        if (str_ends_with($trimmed, ',') && str_contains($text, '"objects"')) {
            $lastComplete = strrpos($trimmed, '},');
            if ($lastComplete !== false) {
                $try = substr($trimmed, 0, $lastComplete + 1).']}';
                $decoded = json_decode($try, true, 512, $decodeFlags);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
            }
        }
        Log::warning('GeminiDesignService: JSON parse failed', [
            'json_error' => $err,
            'text_preview' => mb_substr($text, 0, 400),
            'text_length' => strlen($text),
        ]);

        return null;
    }
}
