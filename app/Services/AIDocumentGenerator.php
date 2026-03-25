<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Advanced AI Document Generator
 * Creates professional document designs with vectors, images, gradients, and rich typography.
 */
class AIDocumentGenerator
{
    protected ?string $apiKey = null;

    protected string $model = 'gpt-4o-mini';

    protected string $baseUrl = 'https://api.openai.com/v1';

    protected int $timeout = 45;

    protected int $maxTokens = 4000;

    public function __construct()
    {
        $this->apiKey = Setting::get('openai_api_key') ?: env('OPENAI_API_KEY');
        $this->model = Setting::get('openai_model') ?: env('OPENAI_MODEL', 'gpt-4o-mini');
        $this->baseUrl = rtrim(Setting::get('openai_base_url') ?: env('OPENAI_BASE_URL', 'https://api.openai.com/v1'), '/');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->apiKey);
    }

    /**
     * Convert design data (from AI) to Fabric.js-compatible JSON. Public for use by Gemini flow.
     */
    public function convertDesignDataToFabric(array $designData, int $width, int $height): array
    {
        return $this->convertToFabricJson($designData, $width, $height);
    }

    /**
     * Generate an advanced letter/document design with vectors, images, gradients.
     */
    public function generateLetter(string $prompt, int $width = 595, int $height = 842): array
    {
        $systemPrompt = $this->buildAdvancedDocumentPrompt($width, $height, 'letter');

        return $this->generate($prompt, $systemPrompt, $width, $height);
    }

    /**
     * Generate a generic document design (brochure, flyer, etc.)
     */
    public function generateDocument(string $prompt, string $documentType = 'document', int $width = 595, int $height = 842): array
    {
        $systemPrompt = $this->buildAdvancedDocumentPrompt($width, $height, $documentType);

        return $this->generate($prompt, $systemPrompt, $width, $height);
    }

    protected function buildAdvancedDocumentPrompt(int $width, int $height, string $docType): string
    {
        return <<<PROMPT
You are an expert graphic designer creating stunning, professional document layouts. Generate a JSON structure for a Fabric.js canvas that produces AMAZING designs.

CANVAS: {$width}x{$height} pixels. Document type: {$docType}.

RESPONSE FORMAT - Valid JSON only:
{
  "backgroundColor": "#hexcolor or linear-gradient(...)",
  "objects": [
    {
      "type": "rect|circle|ellipse|triangle|polygon|path|line|textbox|image",
      "left": number,
      "top": number,
      "width": number (rect/triangle/textbox/image),
      "height": number (rect/triangle/textbox/image),
      "radius": number (circle),
      "rx": number, "ry": number (ellipse),
      "points": [[x,y],[x,y],...] (polygon),
      "path": [["M",x,y],["L",x,y],["C",x1,y1,x2,y2,x,y],["Z"]] (path - SVG commands),
      "x1": number, "y1": number, "x2": number, "y2": number (line),
      "fill": "#hex or gradient object",
      "gradient": {"type":"linear","angle":0-360,"colors":[{"pos":0,"hex":"#xxx"},{"pos":1,"hex":"#xxx"}]},
      "stroke": "#hex",
      "strokeWidth": number,
      "shadow": {"color":"rgba(0,0,0,0.3)","blur":10,"offsetX":2,"offsetY":2},
      "opacity": 0-1,
      "text": "string" (textbox),
      "fontSize": number (textbox),
      "fontFamily": "Arial|Georgia|Times New Roman|Helvetica",
      "fontWeight": "normal|bold",
      "textAlign": "left|center|right",
      "src": "https://placehold.co/400x200/1e3a5f/ffffff?text=Logo" (image - use placehold.co for placeholders)
    }
  ]
}

DESIGN RULES:
- Use VECTORS: rect, circle, ellipse, triangle, polygon, path for decorative elements
- Use GRADIENTS for headers, backgrounds, accents (linear gradients with 2-3 colors)
- Use SHADOWS on cards, boxes, important elements (blur 8-15, offset 2-4)
- Use IMAGE placeholders: "https://placehold.co/WIDTHxHEIGHT/COLOR/ffffff?text=TEXT" for logos, photos
- Create LAYERED designs: header bar (gradient), content area, footer
- Use professional COLOR PALETTES: blues (#1e3a5f, #3b82f6), grays (#64748b), accents
- TYPOGRAPHY: hierarchy with fontSize 24-36 for titles, 12-16 for body
- Add DECORATIVE elements: thin lines, rounded rectangles, subtle shapes
- SPACING: generous margins (40-60px), consistent padding
- For letters: letterhead area, date, recipient block, body text placeholders, signature line
- Make it VISUALLY STUNNING - not bland. Use depth, contrast, modern aesthetics.
PROMPT;
    }

    protected function generate(string $userPrompt, string $systemPrompt, int $width, int $height): array
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException('OpenAI API key is not configured. Please add it in Admin → Settings.');
        }

        set_time_limit($this->timeout + 15); // Allow enough time for HTTP request
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post($this->baseUrl.'/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => "Create an amazing design for: {$userPrompt}"],
                ],
                'temperature' => 0.75,
                'max_tokens' => $this->maxTokens,
                'response_format' => ['type' => 'json_object'],
            ]);

        if (! $response->successful()) {
            Log::error('AIDocumentGenerator API error: '.$response->body());
            throw new \RuntimeException('OpenAI API request failed: '.$response->body());
        }

        $data = $response->json();
        if (! isset($data['choices'][0]['message']['content'])) {
            throw new \RuntimeException('Invalid response from OpenAI');
        }

        $designData = json_decode($data['choices'][0]['message']['content'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Failed to parse AI response: '.json_last_error_msg());
        }

        return $this->convertToFabricJson($designData, $width, $height);
    }

    /**
     * Convert AI response to Fabric.js-compatible JSON.
     */
    protected function convertToFabricJson(array $designData, int $width, int $height): array
    {
        $bg = $designData['backgroundColor'] ?? '#ffffff';
        if (is_string($bg) && str_starts_with($bg, 'linear-gradient')) {
            $bg = '#ffffff'; // Fabric canvas background doesn't support CSS gradients directly
        }

        $fabricDesign = [
            'version' => '5.3.0',
            'objects' => [],
            'background' => $bg,
            'backgroundColor' => $bg,
            'width' => $width,
            'height' => $height,
        ];

        $objects = $designData['objects'] ?? [];
        foreach ($objects as $obj) {
            $fabricObj = $this->convertObjectToFabric($obj, $width, $height);
            if ($fabricObj) {
                $fabricDesign['objects'][] = $fabricObj;
            }
        }

        return $fabricDesign;
    }

    protected function convertObjectToFabric(array $obj, int $canvasWidth, int $canvasHeight): ?array
    {
        $type = $obj['type'] ?? 'rect';
        $fabric = [
            'left' => $obj['left'] ?? 50,
            'top' => $obj['top'] ?? 50,
            'fill' => $obj['fill'] ?? '#333333',
            'selectable' => true,
            'evented' => true,
        ];

        // Gradient — Fabric 5.3 expects colorStops as [{offset,color},...] and gradientUnits; object-shaped stops break addColorStop().
        if (isset($obj['gradient']) && is_array($obj['gradient'])) {
            $grad = $obj['gradient'];
            $colors = $grad['colors'] ?? [['pos' => 0, 'hex' => '#1e3a5f'], ['pos' => 1, 'hex' => '#3b82f6']];
            $colorStopList = [];
            foreach ($colors as $c) {
                if (! is_array($c)) {
                    continue;
                }
                $pos = min(1, max(0, (float) ($c['pos'] ?? 0)));
                $colorStopList[] = [
                    'offset' => $pos,
                    'color' => $c['hex'] ?? '#333333',
                ];
            }
            if ($colorStopList === []) {
                $colorStopList = [
                    ['offset' => 0, 'color' => '#1e3a5f'],
                    ['offset' => 1, 'color' => '#3b82f6'],
                ];
            }
            $angle = ($grad['angle'] ?? 90) * M_PI / 180;
            $w = (float) ($obj['width'] ?? 200);
            $h = (float) ($obj['height'] ?? 100);
            $x2 = $w * cos($angle);
            $y2 = -$w * sin($angle);
            $fabric['fill'] = [
                'type' => 'linear',
                'gradientUnits' => 'pixels',
                'coords' => [
                    'x1' => 0.0,
                    'y1' => $h,
                    'x2' => $x2,
                    'y2' => $h + $y2,
                ],
                'colorStops' => $colorStopList,
            ];
        }

        // Shadow
        if (isset($obj['shadow']) && is_array($obj['shadow'])) {
            $s = $obj['shadow'];
            $fabric['shadow'] = [
                'color' => $s['color'] ?? 'rgba(0,0,0,0.3)',
                'blur' => $s['blur'] ?? 10,
                'offsetX' => $s['offsetX'] ?? 2,
                'offsetY' => $s['offsetY'] ?? 2,
            ];
        }

        // Opacity
        if (isset($obj['opacity'])) {
            $fabric['opacity'] = min(1, max(0, (float) $obj['opacity']));
        }

        // Stroke
        if (! empty($obj['stroke'])) {
            $fabric['stroke'] = $obj['stroke'];
            $fabric['strokeWidth'] = $obj['strokeWidth'] ?? 1;
        }

        switch ($type) {
            case 'rect':
                $fabric['type'] = 'rect';
                $fabric['width'] = $obj['width'] ?? 200;
                $fabric['height'] = $obj['height'] ?? 100;
                if (isset($obj['rx'])) {
                    $fabric['rx'] = $obj['rx'];
                }
                if (isset($obj['ry'])) {
                    $fabric['ry'] = $obj['ry'] ?? $obj['rx'];
                }
                break;

            case 'circle':
                $fabric['type'] = 'circle';
                $fabric['radius'] = $obj['radius'] ?? 50;
                break;

            case 'ellipse':
                $fabric['type'] = 'ellipse';
                $fabric['rx'] = $obj['rx'] ?? 80;
                $fabric['ry'] = $obj['ry'] ?? 40;
                break;

            case 'triangle':
                $fabric['type'] = 'triangle';
                $fabric['width'] = $obj['width'] ?? 100;
                $fabric['height'] = $obj['height'] ?? 100;
                break;

            case 'polygon':
                $fabric['type'] = 'polygon';
                $fabric['points'] = $obj['points'] ?? [[0, 0], [100, 0], [50, 87]];
                break;

            case 'path':
                $fabric['type'] = 'path';
                $pathData = $obj['path'] ?? null;
                if ($pathData && is_array($pathData)) {
                    $fabric['path'] = $pathData;
                } else {
                    return null;
                }
                break;

            case 'line':
                $fabric['type'] = 'line';
                $fabric['x1'] = $obj['x1'] ?? 0;
                $fabric['y1'] = $obj['y1'] ?? 0;
                $fabric['x2'] = $obj['x2'] ?? 200;
                $fabric['y2'] = $obj['y2'] ?? 0;
                break;

            case 'textbox':
                $fabric['type'] = 'textbox';
                $fabric['text'] = $obj['text'] ?? 'Text';
                $fabric['width'] = $obj['width'] ?? 400;
                $fabric['height'] = $obj['height'] ?? 50;
                $fabric['fontSize'] = $obj['fontSize'] ?? 16;
                $fabric['fontFamily'] = $obj['fontFamily'] ?? 'Arial';
                $fabric['fontWeight'] = $obj['fontWeight'] ?? 'normal';
                $fabric['textAlign'] = $obj['textAlign'] ?? 'left';
                if (isset($obj['lineHeight'])) {
                    $fabric['lineHeight'] = $obj['lineHeight'];
                }
                break;

            case 'image':
                $src = $obj['src'] ?? 'https://placehold.co/400x200/e2e8f0/64748b?text=Image';
                $fabric['type'] = 'image';
                $fabric['src'] = $src;
                $fabric['width'] = $obj['width'] ?? 200;
                $fabric['height'] = $obj['height'] ?? 120;
                $fabric['crossOrigin'] = 'anonymous';
                break;

            default:
                $fabric['type'] = 'rect';
                $fabric['width'] = $obj['width'] ?? 200;
                $fabric['height'] = $obj['height'] ?? 100;
        }

        [$gw, $gh] = $this->fabricObjectDimensions($fabric);
        $fabric['fill'] = $this->sanitizeFabricFill($fabric['fill'] ?? '#333333', $gw, $gh);
        if (isset($fabric['stroke'])) {
            $fabric['stroke'] = $this->sanitizeFabricStroke($fabric['stroke']);
        }

        return $fabric;
    }

    /**
     * @return array{0: float, 1: float}
     */
    protected function fabricObjectDimensions(array $fabric): array
    {
        $type = $fabric['type'] ?? 'rect';
        if ($type === 'circle') {
            $r = max(1.0, (float) ($fabric['radius'] ?? 50));

            return [2 * $r, 2 * $r];
        }
        if ($type === 'ellipse') {
            return [
                max(1.0, 2 * (float) ($fabric['rx'] ?? 80)),
                max(1.0, 2 * (float) ($fabric['ry'] ?? 40)),
            ];
        }
        if ($type === 'line') {
            return [
                max(1.0, abs((float) ($fabric['x2'] ?? 0) - (float) ($fabric['x1'] ?? 0))),
                max(1.0, abs((float) ($fabric['y2'] ?? 0) - (float) ($fabric['y1'] ?? 0))),
            ];
        }

        return [
            max(1.0, (float) ($fabric['width'] ?? 200)),
            max(1.0, (float) ($fabric['height'] ?? 100)),
        ];
    }

    protected function finiteNumber(mixed $v, float $fallback): float
    {
        if (is_numeric($v)) {
            $f = (float) $v;

            return is_finite($f) ? $f : $fallback;
        }

        return $fallback;
    }

    /**
     * @return list<array{offset: float, color: string}>
     */
    protected function normalizeGradientColorStopsForFabric(mixed $raw): array
    {
        $out = [];
        if (! is_array($raw)) {
            return $out;
        }

        $values = array_values($raw);
        $allStrings = $values !== [] && array_reduce(
            $values,
            static fn (bool $carry, mixed $item): bool => $carry && is_string($item),
            true
        );
        if ($allStrings) {
            $n = count($values);
            foreach ($values as $i => $color) {
                $out[] = [
                    'offset' => $n <= 1 ? 0.0 : $i / ($n - 1),
                    'color' => (string) $color,
                ];
            }

            return $out;
        }

        foreach ($raw as $key => $item) {
            if (is_string($item) && is_numeric($key)) {
                $out[] = [
                    'offset' => min(1.0, max(0.0, (float) $key)),
                    'color' => $item,
                ];
            } elseif (is_array($item)) {
                if (isset($item['offset'], $item['color'])) {
                    $out[] = [
                        'offset' => min(1.0, max(0.0, (float) $item['offset'])),
                        'color' => (string) $item['color'],
                    ];
                } elseif (isset($item['pos'], $item['hex'])) {
                    $out[] = [
                        'offset' => min(1.0, max(0.0, (float) $item['pos'])),
                        'color' => (string) $item['hex'],
                    ];
                }
            }
        }
        usort($out, static fn (array $a, array $b): int => $a['offset'] <=> $b['offset']);

        return $out;
    }

    protected function sanitizeFabricFill(mixed $fill, float $w, float $h): mixed
    {
        if (is_string($fill)) {
            if (str_starts_with($fill, 'linear-gradient') || str_starts_with($fill, 'radial-gradient')) {
                return '#f1f5f9';
            }

            return $fill;
        }
        if (! is_array($fill)) {
            return '#333333';
        }

        $type = $fill['type'] ?? '';
        if (! in_array($type, ['linear', 'radial'], true)) {
            return '#333333';
        }

        $stops = $this->normalizeGradientColorStopsForFabric($fill['colorStops'] ?? []);
        if (count($stops) < 2) {
            $stops = [
                ['offset' => 0.0, 'color' => '#64748b'],
                ['offset' => 1.0, 'color' => '#0f172a'],
            ];
        }

        if ($type === 'linear') {
            $coords = is_array($fill['coords'] ?? null) ? $fill['coords'] : [];
            $merged = array_merge(
                ['x1' => 0.0, 'y1' => 0.0, 'x2' => $w, 'y2' => 0.0],
                array_intersect_key($coords, ['x1' => true, 'y1' => true, 'x2' => true, 'y2' => true])
            );
            foreach (['x1', 'y1', 'x2', 'y2'] as $k) {
                $merged[$k] = $this->finiteNumber($merged[$k] ?? null, ['x1' => 0.0, 'y1' => 0.0, 'x2' => $w, 'y2' => 0.0][$k]);
            }

            return [
                'type' => 'linear',
                'gradientUnits' => 'pixels',
                'coords' => $merged,
                'colorStops' => $stops,
            ];
        }

        $defaults = [
            'x1' => $w / 2,
            'y1' => $h / 2,
            'r1' => 0.0,
            'x2' => $w / 2,
            'y2' => $h / 2,
            'r2' => max($w, $h) / 2,
        ];
        $coords = is_array($fill['coords'] ?? null) ? $fill['coords'] : [];
        $merged = array_merge($defaults, array_intersect_key($coords, $defaults));
        foreach ($defaults as $k => $def) {
            $merged[$k] = $this->finiteNumber($merged[$k] ?? null, $def);
        }
        if ($merged['r2'] <= 0) {
            $merged['r2'] = max(1.0, max($w, $h) / 2);
        }

        return [
            'type' => 'radial',
            'gradientUnits' => 'pixels',
            'coords' => $merged,
            'colorStops' => $stops,
        ];
    }

    protected function sanitizeFabricStroke(mixed $stroke): string
    {
        if (is_string($stroke) && $stroke !== '') {
            if (str_starts_with($stroke, 'linear-gradient') || str_starts_with($stroke, 'radial-gradient')) {
                return '#334155';
            }

            return $stroke;
        }

        return '#334155';
    }
}
