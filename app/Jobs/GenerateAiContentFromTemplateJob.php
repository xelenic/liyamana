<?php

namespace App\Jobs;

use App\Models\AiContentGeneration;
use App\Models\AiContentTemplate;
use App\Models\CreditTransaction;
use App\Models\User;
use App\Notifications\AppNotification;
use App\Services\AiContentCreditService;
use App\Services\GeminiDesignService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GenerateAiContentFromTemplateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Multi-page Gemini generation often exceeds 2 minutes; the queue worker
     * sends SIGKILL (exit 137) when this is exceeded. Keep in sync with
     * DB_QUEUE_RETRY_AFTER and `php artisan queue:work --timeout=...`.
     */
    public int $timeout = 600;

    /** Avoid duplicate API runs / double credit if a worker is killed mid-job. */
    public int $tries = 1;

    public function __construct(
        public int $templateId,
        public array $fieldValues,
        public string $token,
        public ?int $userId = null
    ) {}

    public function handle(): void
    {
        $template = AiContentTemplate::active()->find($this->templateId);
        if (! $template) {
            Cache::put('ai_content_result_'.$this->token, ['error' => 'Template not found.'], 3600);

            return;
        }

        $prompt = $template->prompt;
        foreach ($this->fieldValues as $key => $value) {
            $prompt = str_replace(['{{'.$key.'}}', '{{ '.$key.' }}'], (string) $value, $prompt);
        }

        $pageCount = $template->resolvePageCount($prompt);

        $width = 800;
        $height = 1000;
        if ($template->editor_json && isset($template->editor_json['pages'][0]['data'])) {
            try {
                $firstPage = is_string($template->editor_json['pages'][0]['data'])
                    ? json_decode($template->editor_json['pages'][0]['data'], true)
                    : $template->editor_json['pages'][0]['data'];
                $width = $firstPage['width'] ?? 800;
                $height = $firstPage['height'] ?? 1000;
            } catch (\Throwable $e) {
                // use defaults
            }
        }

        try {
            $service = app(GeminiDesignService::class);
            $multi = $service->generateMultiPage($prompt, $width, $height, $pageCount);
            $fabricPages = $multi['pages'];
            $usage = $multi['usage'] ?? null;
        } catch (\Throwable $e) {
            Log::error('GenerateAiContentFromTemplateJob failed', [
                'template_id' => $this->templateId,
                'token' => $this->token,
                'message' => $e->getMessage(),
            ]);
            Cache::put('ai_content_result_'.$this->token, [
                'error' => $e->getMessage(),
            ], 3600);

            return;
        }

        $creditCost = AiContentCreditService::computeCost($usage);
        if ($creditCost > 0 && $this->userId) {
            $user = User::find($this->userId);
            if ($user && (float) ($user->balance ?? 0) >= $creditCost) {
                $user->decrement('balance', $creditCost);
                CreditTransaction::create([
                    'user_id' => $user->id,
                    'amount' => -$creditCost,
                    'type' => 'purchase',
                    'balance_after' => $user->fresh()->balance,
                    'payment_method' => 'platform_credit',
                    'reference' => 'ai_content_template',
                    'description' => 'AI content template: '.($template->name ?? ''),
                ]);
            }
        }

        $designId = uniqid('design_');
        $pagesPayload = array_map(static fn (array $fabric) => json_encode($fabric), $fabricPages);
        $payload = [
            'design_id' => $designId,
            'name' => $template->name.' - '.now()->format('M d, H:i'),
            'pages' => $pagesPayload,
            'is_multi_page' => true,
            'page_count' => count($pagesPayload),
            'thumbnail' => null,
            'type' => 'document',
            'created_at' => now()->toDateTimeString(),
        ];

        if ($this->userId) {
            $user = User::find($this->userId);
            if ($user) {
                try {
                    $generation = AiContentGeneration::create([
                        'user_id' => $user->id,
                        'ai_content_template_id' => $template->id,
                        'design_session_id' => $designId,
                        'name' => $payload['name'],
                        'pages' => $pagesPayload,
                        'is_multi_page' => true,
                        'page_count' => count($pagesPayload),
                        'type' => 'document',
                        'thumbnail' => null,
                    ]);
                    $openUrl = route('design.aiContentGenerations.open', $generation);
                    $user->notify(new AppNotification(
                        'AI design ready',
                        'Your AI-generated content from “'.($template->name ?? 'template').'” is ready. Open it in the editor.',
                        $openUrl,
                        'success'
                    ));
                } catch (\Throwable $e) {
                    Log::warning('GenerateAiContentFromTemplateJob: could not save generation or notify', [
                        'template_id' => $this->templateId,
                        'user_id' => $this->userId,
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        }

        Cache::put('ai_content_result_'.$this->token, $payload, 3600);
    }
}
