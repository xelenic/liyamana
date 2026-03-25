<?php

namespace Modules\NanoBananaModule\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GeminiApiService
{
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function isConfigured(): bool
    {
        return ! empty($this->getApiKey());
    }

    protected function getApiKey(): ?string
    {
        return Setting::get('gemini_api_key') ?: config('services.gemini.api_key');
    }

    protected function getModel(): string
    {
        return Setting::get('gemini_image_model', 'gemini-3-pro-image-preview');
    }

    /**
     * Generate image from text prompt using Gemini native image generation.
     */
    public function generateImage(string $prompt, string $aspectRatio = '1:1', string $resolution = '2K'): array
    {
        return $this->callGemini($prompt, [], $aspectRatio, $resolution);
    }

    /**
     * Generate image with input image (image-to-image edit) using Gemini.
     */
    public function editImage(string $prompt, array $imageUrls, string $aspectRatio = '1:1', string $resolution = '2K'): array
    {
        return $this->callGemini($prompt, $imageUrls, $aspectRatio, $resolution);
    }

    protected function callGemini(string $prompt, array $imageUrls, string $aspectRatio, string $resolution): array
    {
        $apiKey = $this->getApiKey();
        if (! $apiKey) {
            return ['success' => false, 'error' => 'Gemini API key not configured'];
        }

        $model = $this->getModel();
        $modelSupportsImageConfig = str_contains($model, 'gemini-3-pro-image');

        $url = $this->baseUrl . '/' . $model . ':generateContent?key=' . $apiKey;

        $parts = [['text' => $prompt]];

        foreach ($imageUrls as $imageUrl) {
            $imageData = $this->resolveImageToBase64($imageUrl);
            if ($imageData) {
                $parts[] = [
                    'inline_data' => [
                        'mime_type' => $imageData['mime_type'],
                        'data' => $imageData['data'],
                    ],
                ];
            }
        }

        $body = [
            'contents' => [['parts' => $parts]],
            'generationConfig' => [
                'responseModalities' => ['TEXT', 'IMAGE'],
            ],
        ];

        if ($modelSupportsImageConfig) {
            $body['generationConfig']['imageConfig'] = [
                'aspectRatio' => $aspectRatio === 'auto' ? '1:1' : $aspectRatio,
                'imageSize' => $resolution,
            ];
        }

        try {
            $response = Http::timeout(120)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $body);

            $data = $response->json();

            if (! $response->successful()) {
                $error = $data['error']['message'] ?? $data['error']['status'] ?? $response->body() ?? 'API request failed';
                return ['success' => false, 'error' => $error];
            }

            $imageUrl = $this->extractImageFromResponse($data);
            if ($imageUrl) {
                return ['success' => true, 'image_url' => $imageUrl];
            }

            return ['success' => false, 'error' => 'No image in response'];
        } catch (\Exception $e) {
            Log::error('Gemini API error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function resolveImageToBase64(string $url): ?array
    {
        if (str_starts_with($url, 'data:')) {
            if (preg_match('#^data:([^;]+);base64,(.+)$#', $url, $m)) {
                return ['mime_type' => $m[1], 'data' => $m[2]];
            }
        }

        $mime = 'image/png';
        if (str_contains($url, '.jpg') || str_contains($url, '.jpeg')) {
            $mime = 'image/jpeg';
        } elseif (str_contains($url, '.webp')) {
            $mime = 'image/webp';
        }

        if (str_contains($url, '/storage/')) {
            $path = preg_replace('#^.*?/storage/#', '', $url);
            if (Storage::disk('public')->exists($path)) {
                $contents = Storage::disk('public')->get($path);
                return ['mime_type' => $mime, 'data' => base64_encode($contents)];
            }
        }

        if (str_starts_with($url, '/') || str_starts_with($url, 'http')) {
            $fullUrl = str_starts_with($url, 'http') ? $url : url($url);
            $contents = @file_get_contents($fullUrl);
            if ($contents) {
                return ['mime_type' => $mime, 'data' => base64_encode($contents)];
            }
        }

        return null;
    }

    protected function extractImageFromResponse(array $data): ?string
    {
        $candidates = $data['candidates'] ?? [];
        foreach ($candidates as $candidate) {
            $content = $candidate['content']['parts'] ?? [];
            foreach ($content as $part) {
                $inline = $part['inline_data'] ?? $part['inlineData'] ?? null;
                if ($inline) {
                    $mime = $inline['mime_type'] ?? $inline['mimeType'] ?? 'image/png';
                    $raw = $inline['data'] ?? null;
                    if ($raw) {
                        return $this->storeGeneratedImage($raw, $mime);
                    }
                }
            }
        }
        return null;
    }

    protected function storeGeneratedImage(string $base64Data, string $mimeType): string
    {
        $ext = 'png';
        if (str_contains($mimeType, 'jpeg') || str_contains($mimeType, 'jpg')) {
            $ext = 'jpg';
        } elseif (str_contains($mimeType, 'webp')) {
            $ext = 'webp';
        }
        $path = 'nano-banana-temp/' . Str::uuid() . '.' . $ext;
        $decoded = base64_decode($base64Data, true);
        if ($decoded === false) {
            throw new \RuntimeException('Invalid base64 image data');
        }
        Storage::disk('public')->put($path, $decoded);
        return url(Storage::disk('public')->url($path));
    }
}
