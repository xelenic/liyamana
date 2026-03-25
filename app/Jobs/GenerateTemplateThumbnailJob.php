<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\Setting;
use App\Models\Template;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateTemplateThumbnailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;

    public function __construct(
        public int $templateId,
        public string $prompt,
        public ?int $productId = null
    ) {}

    public function handle(): void
    {
        $template = Template::find($this->templateId);
        if (! $template) {
            Log::warning('GenerateTemplateThumbnailJob: template not found', ['id' => $this->templateId]);

            return;
        }

        $apiKey = Setting::get('gemini_api_key') ?: config('services.gemini.api_key') ?: env('GEMINI_API_KEY');
        if (! $apiKey) {
            Log::error('GenerateTemplateThumbnailJob: Gemini API key not configured');

            return;
        }

        $product = null;
        if ($this->productId) {
            $product = Product::find($this->productId);
            if (! $product || ! $template->products()->where('products.id', $this->productId)->exists()) {
                $product = null;
            }
        }

        $parts = [];
        $hasTemplateImage = $template->thumbnail_path && Storage::disk('public')->exists($template->thumbnail_path);
        $hasProductImage = $product && $product->image && ! str_starts_with($product->image, 'http') && Storage::disk('public')->exists($product->image);

        if ($hasTemplateImage) {
            $currentImageData = Storage::disk('public')->get($template->thumbnail_path);
            $mime = $this->mimeFromPath($template->thumbnail_path);
            $parts[] = [
                'inlineData' => [
                    'mimeType' => $mime,
                    'data' => base64_encode($currentImageData),
                ],
            ];
        }

        if ($hasProductImage) {
            $productImageData = Storage::disk('public')->get($product->image);
            $mime = $this->mimeFromPath($product->image);
            $parts[] = [
                'inlineData' => [
                    'mimeType' => $mime,
                    'data' => base64_encode($productImageData),
                ],
            ];
        }

        if ($hasTemplateImage && $hasProductImage) {
            $parts[] = [
                'text' => 'The FIRST image is the current template thumbnail. The SECOND image is the attached product. Generate a NEW thumbnail image that combines or is inspired by BOTH: the template design and the product. Instruction: '.$this->prompt."\n\nCreate a single cohesive image that can replace the template thumbnail. Return only the new image, no text or explanation.",
            ];
        } elseif ($hasTemplateImage) {
            $parts[] = [
                'text' => 'This is the current thumbnail image for a design template. Generate a NEW thumbnail image based on this image and the following instruction: '.$this->prompt."\n\nCreate a single image that replaces the thumbnail. Return only the new image, no text or explanation.",
            ];
        } elseif ($hasProductImage) {
            $parts[] = [
                'text' => 'The image above is an attached product. Generate a thumbnail image for a design template that incorporates or is inspired by this product. Instruction: '.$this->prompt."\n\nReturn only the new image, no text or explanation.",
            ];
        } else {
            $parts[] = [
                'text' => 'Generate a single thumbnail image for a design template. '.$this->prompt."\n\nReturn only the image, no text or explanation.",
            ];
        }

        $model = Setting::get('gemini_image_model') ?: 'gemini-2.0-flash-exp';
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/'.$model.':generateContent?key='.urlencode($apiKey);

        $body = [
            'contents' => [['parts' => $parts]],
            'generationConfig' => [
                'temperature' => 0.8,
                'maxOutputTokens' => 8192,
                'responseModalities' => ['TEXT', 'IMAGE'],
            ],
        ];

        try {
            $response = Http::connectTimeout(30)
                ->timeout(120)
                ->retry(2, 2000, throw: false)
                ->post($url, $body);
        } catch (\Throwable $e) {
            Log::error('GenerateTemplateThumbnailJob: request failed', [
                'template_id' => $this->templateId,
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }

        if (! $response->successful()) {
            $resBody = $response->json();
            $msg = $resBody['error']['message'] ?? $response->body();
            Log::error('GenerateTemplateThumbnailJob: API error', [
                'template_id' => $this->templateId,
                'message' => $msg,
            ]);
            throw new \RuntimeException('Gemini API error: '.(is_string($msg) ? $msg : json_encode($msg)));
        }

        $data = $response->json();
        $candidates = $data['candidates'] ?? [];
        $imageData = null;

        foreach ($candidates as $candidate) {
            $contentParts = $candidate['content']['parts'] ?? [];
            foreach ($contentParts as $part) {
                if (isset($part['inlineData']['data'])) {
                    $imageData = base64_decode($part['inlineData']['data']);
                    break 2;
                }
            }
        }

        if ($imageData === null || $imageData === false) {
            Log::warning('GenerateTemplateThumbnailJob: no image in response', [
                'template_id' => $this->templateId,
            ]);
            throw new \RuntimeException('No image was generated. Try a different prompt or ensure the model supports image generation.');
        }

        // Store generated thumbnails in a dedicated folder (only DB thumbnail_path is updated)
        $folder = 'template_thumbnails';
        Storage::disk('public')->makeDirectory($folder);
        $filename = $folder.'/template_'.$template->id.'_thumbnail.png';
        Storage::disk('public')->put($filename, $imageData);

        $oldPath = $template->thumbnail_path;
        $template->update(['thumbnail_path' => $filename]);

        // Remove old file if it was in a different path (e.g. templates/) to avoid orphans
        if ($oldPath && $oldPath !== $filename && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        Log::info('GenerateTemplateThumbnailJob: thumbnail saved', [
            'template_id' => $this->templateId,
            'path' => $filename,
        ]);
    }

    /**
     * Get MIME type from file path (extension).
     */
    private function mimeFromPath(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/png',
        };
    }
}
