<?php

namespace App\Http\Controllers;

use App\Jobs\CreateApplicationBackupJob;
use App\Jobs\RestoreApplicationBackupJob;
use App\Models\BackupOperation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminBackupController extends Controller
{
    public function index(Request $request)
    {
        $operations = BackupOperation::query()
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        $highlightUuid = $request->query('op');

        return view('admin.settings.backup-restore', compact('operations', 'highlightUuid'));
    }

    public function createBackup()
    {
        $op = BackupOperation::query()->create([
            'uuid' => Str::uuid()->toString(),
            'user_id' => Auth::id(),
            'type' => BackupOperation::TYPE_BACKUP,
            'status' => BackupOperation::STATUS_PENDING,
            'message' => 'Queued…',
        ]);

        CreateApplicationBackupJob::dispatch($op->id);

        return redirect()
            ->route('admin.settings.backup-restore', ['op' => $op->uuid])
            ->with('success', 'Backup has been queued. Progress updates below; download when status is completed.');
    }

    public function uploadRestore(Request $request)
    {
        $maxBytes = (int) config('backup.max_upload_bytes', 512 * 1024 * 1024);
        $maxKb = max(1, (int) ceil($maxBytes / 1024));

        $request->validate([
            'restore_backup' => [
                'required',
                'file',
                'max:'.$maxKb,
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $value instanceof \Illuminate\Http\UploadedFile) {
                        return;
                    }
                    $ext = strtolower($value->getClientOriginalExtension() ?? '');
                    if ($ext !== 'zip') {
                        $fail('The backup must be a .zip file.');
                    }
                },
            ],
            'restore_confirm' => 'accepted',
        ]);

        $upload = $request->file('restore_backup');
        if ($upload->getSize() > $maxBytes) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'File exceeds maximum allowed size.');
        }

        $uuid = Str::uuid()->toString();
        $relative = 'backups/imports/restore-'.$uuid.'.zip';
        $upload->storeAs('backups/imports', 'restore-'.$uuid.'.zip', 'local');
        $full = Storage::disk('local')->path($relative);

        $op = BackupOperation::query()->create([
            'uuid' => Str::uuid()->toString(),
            'user_id' => Auth::id(),
            'type' => BackupOperation::TYPE_RESTORE,
            'status' => BackupOperation::STATUS_PENDING,
            'message' => 'Queued…',
        ]);

        RestoreApplicationBackupJob::dispatch($op->id, $full);

        return redirect()
            ->route('admin.settings.backup-restore', ['op' => $op->uuid])
            ->with('success', 'Restore has been queued. This replaces the database and storage files from the backup when the job completes.');
    }

    public function download(BackupOperation $backupOperation)
    {
        if ($backupOperation->type !== BackupOperation::TYPE_BACKUP
            || $backupOperation->status !== BackupOperation::STATUS_COMPLETED
            || empty($backupOperation->result_path)) {
            abort(404);
        }

        $path = storage_path('app/private/'.$backupOperation->result_path);
        if (! is_file($path)) {
            abort(404);
        }

        return response()->download($path, 'flipbook-backup-'.$backupOperation->uuid.'.zip');
    }

    public function status(BackupOperation $backupOperation)
    {
        return response()->json([
            'uuid' => $backupOperation->uuid,
            'type' => $backupOperation->type,
            'status' => $backupOperation->status,
            'message' => $backupOperation->message,
            'error_message' => $backupOperation->error_message,
            'finished' => $backupOperation->isFinished(),
            'download_url' => $backupOperation->type === BackupOperation::TYPE_BACKUP
                && $backupOperation->status === BackupOperation::STATUS_COMPLETED
                && $backupOperation->result_path
                ? route('admin.settings.backup-restore.download', $backupOperation)
                : null,
        ]);
    }
}
