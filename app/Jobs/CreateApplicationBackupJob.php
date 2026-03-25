<?php

namespace App\Jobs;

use App\Models\BackupOperation;
use App\Services\ApplicationBackupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class CreateApplicationBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public int $tries = 1;

    public function __construct(
        public int $backupOperationId
    ) {}

    public function handle(ApplicationBackupService $backupService): void
    {
        $op = BackupOperation::query()->find($this->backupOperationId);
        if (! $op || $op->type !== BackupOperation::TYPE_BACKUP) {
            return;
        }

        $op->update([
            'status' => BackupOperation::STATUS_PROCESSING,
            'started_at' => now(),
            'message' => 'Creating archive…',
            'error_message' => null,
        ]);

        $relativePath = 'backups/exports/flipbook-backup-'.$op->uuid.'.zip';
        $fullPath = storage_path('app/private/'.$relativePath);

        try {
            File::ensureDirectoryExists(dirname($fullPath));
            $backupService->createBackupZip($fullPath);

            $op->update([
                'status' => BackupOperation::STATUS_COMPLETED,
                'message' => 'Backup ready.',
                'result_path' => $relativePath,
                'completed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('CreateApplicationBackupJob failed', [
                'id' => $this->backupOperationId,
                'message' => $e->getMessage(),
            ]);

            if (is_file($fullPath)) {
                @unlink($fullPath);
            }

            $op->update([
                'status' => BackupOperation::STATUS_FAILED,
                'message' => 'Backup failed.',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            throw $e;
        }
    }

    public function failed(?\Throwable $e): void
    {
        $op = BackupOperation::query()->find($this->backupOperationId);
        if ($op && $op->status === BackupOperation::STATUS_PROCESSING) {
            $op->update([
                'status' => BackupOperation::STATUS_FAILED,
                'message' => 'Backup failed.',
                'error_message' => $e?->getMessage() ?? 'Unknown error',
                'completed_at' => now(),
            ]);
        }
    }
}
