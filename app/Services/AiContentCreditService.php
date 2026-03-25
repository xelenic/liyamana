<?php

namespace App\Services;

use App\Models\Setting;

/**
 * Compute platform credit cost for AI Content Template generation.
 * Uses token-based billing when enabled and usage is available; otherwise flat cost.
 */
class AiContentCreditService
{
    /**
     * Compute credit cost for one AI content generation.
     *
     * @param  array{input_tokens?: int, output_tokens?: int}|null  $usage  Token usage from Gemini (input_tokens, output_tokens). Omit or null to use flat cost.
     * @return float Credit amount to deduct (>= 0)
     */
    public static function computeCost(?array $usage = null): float
    {
        $useTokenCost = (string) (Setting::get('ai_content_use_token_cost') ?? '1');
        $useTokenCost = $useTokenCost === '1' || $useTokenCost === 'true';

        if ($useTokenCost && $usage) {
            $inputTokens = (int) ($usage['input_tokens'] ?? 0);
            $outputTokens = (int) ($usage['output_tokens'] ?? 0);
            if ($inputTokens > 0 || $outputTokens > 0) {
                $inPer1000 = self::parseCostSetting(Setting::get('ai_content_input_token_cost_per_1000'), 0.01);
                $outPer1000 = self::parseCostSetting(Setting::get('ai_content_output_token_cost_per_1000'), 0.02);
                $cost = ($inputTokens / 1000.0) * $inPer1000 + ($outputTokens / 1000.0) * $outPer1000;

                return round(max(0.0, $cost), 4);
            }
        }

        $raw = Setting::get('ai_content_template_credit_cost', '0.5');
        $flat = ($raw === '' || $raw === null || ! is_numeric($raw)) ? 0.5 : (float) $raw;

        return max(0.0, round($flat, 4));
    }

    private static function parseCostSetting(?string $value, float $default): float
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return $default;
        }

        return max(0.0, (float) $value);
    }
}
