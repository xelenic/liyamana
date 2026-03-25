<?php

namespace App\Console\Commands;

use App\Models\CreditTransaction;
use App\Models\Order;
use App\Models\ScheduledMail;
use App\Services\DesignCheckoutStockService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessScheduledMailsCommand extends Command
{
    protected $signature = 'scheduled-mail:process';

    protected $description = 'Process due scheduled mails: deduct credit and create order (or fulfill prepaid send-letter)';

    public function handle(): int
    {
        $due = ScheduledMail::where('status', 'pending')
            ->where('send_at', '<=', now())
            ->orderBy('send_at')
            ->get();

        foreach ($due as $scheduled) {
            $this->processOne($scheduled);
        }

        return self::SUCCESS;
    }

    private function processOne(ScheduledMail $scheduled): void
    {
        $checkoutData = $scheduled->checkout_data ?? [];
        $prepaid = ! empty($checkoutData['scheduled_prepaid']);

        if ($prepaid) {
            $this->processPrepaidScheduled($scheduled, $checkoutData);

            return;
        }

        $user = $scheduled->user;
        $balance = (float) ($user->balance ?? 0);
        $amount = (float) $scheduled->credit_amount;

        if ($balance < $amount) {
            $scheduled->update([
                'status' => 'failed',
                'error_message' => 'Insufficient balance. Required: '.$amount.', available: '.$balance,
            ]);

            return;
        }

        try {
            $user->decrement('balance', $amount);
            CreditTransaction::create([
                'user_id' => $user->id,
                'amount' => -$amount,
                'type' => 'purchase',
                'balance_after' => $user->fresh()->balance,
                'payment_method' => 'platform_credit',
                'reference' => 'scheduled_mail',
                'description' => 'Scheduled mail: '.$scheduled->template_name,
            ]);

            $checkoutData = $scheduled->checkout_data ?? [];
            $order = Order::create([
                'user_id' => $scheduled->user_id,
                'template_id' => $scheduled->template_id,
                'template_name' => $scheduled->template_name,
                'quantity' => $scheduled->quantity,
                'total_amount' => $amount,
                'payment_method' => 'platform_credit',
                'status' => 'completed',
                'delivery_status' => 'pending',
                'checkout_data' => $checkoutData,
            ]);

            $scheduled->update([
                'status' => 'sent',
                'order_id' => $order->id,
                'error_message' => null,
            ]);
        } catch (\Throwable $e) {
            $scheduled->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            if (isset($user) && isset($amount)) {
                $user->increment('balance', $amount);
            }
        }
    }

    /**
     * Send-letter checkout paid upfront (credits or Stripe); deduct stock and create order at send_at.
     */
    private function processPrepaidScheduled(ScheduledMail $scheduled, array $checkoutData): void
    {
        $user = $scheduled->user;
        $amount = (float) $scheduled->credit_amount;
        $paymentMethodStored = $checkoutData['scheduled_payment_method'] ?? 'stripe';
        if (! in_array($paymentMethodStored, ['stripe', 'platform_credit'], true)) {
            $paymentMethodStored = 'stripe';
        }

        $template = $scheduled->template;
        $pageCount = (int) ($template?->page_count ?? 0);

        $stockContext = [
            'template_id' => $scheduled->template_id,
            'template_page_count' => $pageCount,
            'checkout_data' => $checkoutData,
        ];

        $stockService = app(DesignCheckoutStockService::class);

        try {
            DB::transaction(function () use ($scheduled, $stockService, $stockContext, $checkoutData, $amount, $paymentMethodStored) {
                $stockService->deduct($stockContext);

                $order = Order::create([
                    'user_id' => $scheduled->user_id,
                    'template_id' => $scheduled->template_id,
                    'template_name' => $scheduled->template_name,
                    'quantity' => $scheduled->quantity,
                    'total_amount' => $amount,
                    'payment_method' => $paymentMethodStored,
                    'status' => 'completed',
                    'delivery_status' => 'pending',
                    'checkout_data' => $checkoutData,
                ]);

                $scheduled->update([
                    'status' => 'sent',
                    'order_id' => $order->id,
                    'error_message' => null,
                ]);
            });
        } catch (\Throwable $e) {
            $scheduled->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            if ($paymentMethodStored === 'platform_credit' && $amount > 0 && $user) {
                $user->increment('balance', $amount);
                CreditTransaction::create([
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'type' => 'refund',
                    'balance_after' => $user->fresh()->balance,
                    'payment_method' => 'platform_credit',
                    'reference' => 'scheduled_letter_failed',
                    'description' => 'Refund — scheduled letter could not be fulfilled: '.$scheduled->template_name,
                ]);
            } else {
                Log::critical('Scheduled prepaid letter failed after payment (manual follow-up may be required)', [
                    'scheduled_mail_id' => $scheduled->id,
                    'user_id' => $scheduled->user_id,
                    'payment_method' => $paymentMethodStored,
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }
}
