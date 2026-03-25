<?php

namespace App\Jobs;

use App\Models\BackupOperation;
use App\Services\ApplicationBackupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RestoreApplicationBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public int $tries = 1;

    public function __construct(
        public int $backupOperationId,
        public string $absoluteZipPath
    ) {}

    public function handle(ApplicationBackupService $backupService): void
    {
        $op = BackupOperation::query()->find($this->backupOperationId);
        if (! $op || $op->type !== BackupOperation::TYPE_RESTORE) {
            return;
        }

        $op->update([
            'status' => BackupOperation::STATUS_PROCESSING,
            'started_at' => now(),
            'message' => 'Restoring from backup…',
            'error_message' => null,
        ]);

        try {
            if (! is_file($this->absoluteZipPath)) {
                throw new \RuntimeException('Uploaded backup file is no longer available.');
            }

            $backupService->restoreFromZip($this->absoluteZipPath);

            $op->update([
                'status' => BackupOperation::STATUS_COMPLETED,
                'message' => 'Restore completed.',
                'completed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('RestoreApplicationBackupJob failed', [
                'id' => $this->backupOperationId,
                'message' => $e->getMessage(),
            ]);

            $op->update([
                'status' => BackupOperation::STATUS_FAILED,
                'message' => 'Restore failed.',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            throw $e;
        } finally {
            if (is_file($this->absoluteZipPath)) {
                @unlink($this->absoluteZipPath);
            }
        }
    }

    public function failed(?\Throwable $e): void
    {
        if (is_file($this->absoluteZipPath)) {
            @unlink($this->absoluteZipPath);
        }

        $op = BackupOperation::query()->find($this->backupOperationId);
        if ($op && $op->status === BackupOperation::STATUS_PROCESSING) {
            $op->update([
                'status' => BackupOperation::STATUS_FAILED,
                'message' => 'Restore failed.',
                'error_message' => $e?->getMessage() ?? 'Unknown error',
                'completed_at' => now(),
            ]);
        }
    }
}
