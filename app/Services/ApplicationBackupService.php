<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use ZipArchive;

class ApplicationBackupService
{
    protected function isMysqlFamily(?string $driver): bool
    {
        return in_array($driver, ['mysql', 'mariadb'], true);
    }

    public function createBackupZip(string $absoluteZipPath): void
    {
        $dir = dirname($absoluteZipPath);
        if (! is_dir($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $zip = new ZipArchive;
        if ($zip->open($absoluteZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Could not create backup zip file.');
        }

        $tmpDb = null;
        $dumpPath = null;

        try {
            $manifest = $this->buildManifest();
            $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            $driver = config('database.default');
            $conn = config("database.connections.{$driver}");

            if (($conn['driver'] ?? '') === 'sqlite') {
                $tmpDb = sys_get_temp_dir().'/flipbook-backup-db-'.uniqid('', true).'.sqlite';
                $this->copySqliteDatabaseTo($tmpDb);
                $zip->addFile($tmpDb, 'database/database.sqlite');
            } elseif (in_array($conn['driver'] ?? '', ['mysql', 'mariadb'], true)) {
                $dumpPath = sys_get_temp_dir().'/flipbook-backup-dump-'.uniqid('', true).'.sql';
                $this->createMysqlDump($conn, $dumpPath);
                $zip->addFile($dumpPath, 'database/dump.sql');
            } else {
                throw new \RuntimeException('Unsupported database driver for backup: '.($conn['driver'] ?? 'unknown'));
            }

            $publicRoot = storage_path('app/public');
            if (is_dir($publicRoot)) {
                $this->addDirectoryToZip($zip, $publicRoot, 'storage/public');
            }

            $privateRoot = storage_path('app/private');
            if (is_dir($privateRoot)) {
                $this->addDirectoryToZip($zip, $privateRoot, 'storage/private', config('backup.exclude_private_subpaths', []));
            }
        } finally {
            $zip->close();
            if ($tmpDb !== null && is_file($tmpDb)) {
                @unlink($tmpDb);
            }
            if ($dumpPath !== null && is_file($dumpPath)) {
                @unlink($dumpPath);
            }
        }
    }

    /**
     * @return array{version:int, app:string, created_at:string, laravel_version:string, php_version:string, db_driver:string}
     */
    protected function buildManifest(): array
    {
        $driver = config('database.default');
        $conn = config("database.connections.{$driver}");

        return [
            'version' => (int) config('backup.format_version', 1),
            'app' => 'FlipBook',
            'created_at' => now()->toIso8601String(),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'db_driver' => (string) ($conn['driver'] ?? $driver),
        ];
    }

    public function restoreFromZip(string $absoluteZipPath): void
    {
        $this->assertZipSafe($absoluteZipPath);

        $tmp = sys_get_temp_dir().'/flipbook-restore-'.uniqid('', true);
        File::makeDirectory($tmp, 0755, true);

        try {
            $zip = new ZipArchive;
            if ($zip->open($absoluteZipPath) !== true) {
                throw new \RuntimeException('Could not open backup zip.');
            }
            $zip->extractTo($tmp);
            $zip->close();

            $manifestPath = $tmp.'/manifest.json';
            if (! is_file($manifestPath)) {
                throw new \RuntimeException('Invalid backup: manifest.json missing.');
            }

            $manifest = json_decode((string) file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($manifest) || (int) ($manifest['version'] ?? 0) !== (int) config('backup.format_version', 1)) {
                throw new \RuntimeException('Unsupported or missing backup format version.');
            }

            $driver = config('database.default');
            $conn = config("database.connections.{$driver}");
            $manifestDriver = (string) ($manifest['db_driver'] ?? '');

            if (($conn['driver'] ?? '') === 'sqlite' && $manifestDriver === 'sqlite') {
                $sqliteSrc = $tmp.'/database/database.sqlite';
                if (! is_file($sqliteSrc)) {
                    throw new \RuntimeException('Invalid backup: database/database.sqlite missing.');
                }
                $this->restoreSqliteFrom($sqliteSrc);
            } elseif ($this->isMysqlFamily($conn['driver'] ?? null) && $this->isMysqlFamily($manifestDriver)) {
                $sqlSrc = $tmp.'/database/dump.sql';
                if (! is_file($sqlSrc)) {
                    throw new \RuntimeException('Invalid backup: database/dump.sql missing.');
                }
                $this->restoreMysqlFromDump($conn, $sqlSrc);
            } else {
                throw new \RuntimeException(
                    'Backup database type ('.$manifestDriver.') does not match current connection ('.($conn['driver'] ?? '').'). Use the same driver (e.g. sqlite→sqlite or mysql→mysql) or import manually.'
                );
            }

            $this->mirrorStorageFromExtract($tmp.'/storage/public', storage_path('app/public'));
            $this->mirrorStorageFromExtract($tmp.'/storage/private', storage_path('app/private'), config('backup.exclude_private_subpaths', []));
        } finally {
            File::deleteDirectory($tmp);
        }

        try {
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('config:clear');
        } catch (\Throwable $e) {
            Log::warning('backup_restore_artisan_clear_failed', ['message' => $e->getMessage()]);
        }
    }

    protected function assertZipSafe(string $absoluteZipPath): void
    {
        $zip = new ZipArchive;
        if ($zip->open($absoluteZipPath) !== true) {
            throw new \RuntimeException('Could not read backup zip.');
        }

        $maxEntries = (int) config('backup.max_zip_entries', 100_000);
        $maxUncompressed = (int) config('backup.max_uncompressed_bytes', 2 * 1024 * 1024 * 1024);
        $totalUncompressed = 0;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            if ($i >= $maxEntries) {
                $zip->close();
                throw new \RuntimeException('Backup zip has too many entries.');
            }
            $stat = $zip->statIndex($i);
            if ($stat === false) {
                continue;
            }
            $name = (string) ($stat['name'] ?? '');
            if ($name === '' || str_contains($name, '..')) {
                $zip->close();
                throw new \RuntimeException('Invalid path inside backup zip.');
            }
            $totalUncompressed += (int) ($stat['size'] ?? 0);
            if ($totalUncompressed > $maxUncompressed) {
                $zip->close();
                throw new \RuntimeException('Backup zip uncompressed size exceeds safety limit.');
            }
        }
        $zip->close();
    }

    protected function copySqliteDatabaseTo(string $destPath): void
    {
        $default = config('database.default');
        $config = config("database.connections.{$default}");
        if (($config['driver'] ?? '') !== 'sqlite') {
            throw new \RuntimeException('SQLite backup expected sqlite connection.');
        }
        $database = (string) $config['database'];
        if ($database === '' || ! is_file($database)) {
            throw new \RuntimeException('SQLite database file not found.');
        }

        DB::disconnect();
        try {
            if (! @copy($database, $destPath)) {
                throw new \RuntimeException('Could not copy SQLite database for backup.');
            }
        } finally {
            DB::reconnect();
        }
    }

    protected function restoreSqliteFrom(string $sourcePath): void
    {
        $default = config('database.default');
        $config = config("database.connections.{$default}");
        if (($config['driver'] ?? '') !== 'sqlite') {
            throw new \RuntimeException('Restore requires sqlite connection.');
        }
        $database = (string) $config['database'];
        if ($database === '') {
            throw new \RuntimeException('SQLite database path not configured.');
        }

        $dbDir = dirname($database);
        if (! is_dir($dbDir)) {
            File::makeDirectory($dbDir, 0755, true);
        }

        DB::disconnect();
        try {
            if (! is_file($sourcePath)) {
                throw new \RuntimeException('SQLite backup file missing.');
            }
            if (! @copy($sourcePath, $database)) {
                throw new \RuntimeException('Could not replace SQLite database.');
            }
        } finally {
            DB::reconnect();
        }
    }

    /**
     * @param  array<string, mixed>  $conn
     */
    protected function createMysqlDump(array $conn, string $destPath): void
    {
        $binary = $this->findBinary('mysqldump');
        if ($binary === null) {
            throw new \RuntimeException('mysqldump not found in PATH. Install MySQL client tools or use SQLite for backups.');
        }

        $host = (string) ($conn['host'] ?? '127.0.0.1');
        $port = (string) ($conn['port'] ?? '3306');
        $database = (string) ($conn['database'] ?? '');
        $username = (string) ($conn['username'] ?? 'root');
        $password = (string) ($conn['password'] ?? '');

        $process = new Process([
            $binary,
            '--host='.$host,
            '--port='.$port,
            '--user='.$username,
            '--single-transaction',
            '--quick',
            '--lock-tables=false',
            $database,
        ]);
        $process->setTimeout(3600);
        $process->setEnv(array_merge($_ENV, ['MYSQL_PWD' => $password]));
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException('mysqldump failed: '.$process->getErrorOutput());
        }

        File::put($destPath, $process->getOutput());
    }

    /**
     * @param  array<string, mixed>  $conn
     */
    protected function restoreMysqlFromDump(array $conn, string $sqlPath): void
    {
        $binary = $this->findBinary('mysql');
        if ($binary === null) {
            throw new \RuntimeException('mysql client not found in PATH.');
        }

        $host = (string) ($conn['host'] ?? '127.0.0.1');
        $port = (string) ($conn['port'] ?? '3306');
        $database = (string) ($conn['database'] ?? '');
        $username = (string) ($conn['username'] ?? 'root');
        $password = (string) ($conn['password'] ?? '');

        $process = new Process([
            $binary,
            '--host='.$host,
            '--port='.$port,
            '--user='.$username,
            $database,
        ]);
        $process->setTimeout(3600);
        $process->setEnv(array_merge($_ENV, ['MYSQL_PWD' => $password]));
        $in = fopen($sqlPath, 'rb');
        if ($in === false) {
            throw new \RuntimeException('Could not read SQL dump.');
        }
        $process->setInput($in);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException('mysql import failed: '.$process->getErrorOutput());
        }
    }

    protected function findBinary(string $name): ?string
    {
        $paths = array_filter(array_merge(
            ['/usr/bin', '/usr/local/bin', '/opt/homebrew/bin'],
            explode(PATH_SEPARATOR, (string) getenv('PATH'))
        ));
        foreach ($paths as $dir) {
            $dir = rtrim($dir, DIRECTORY_SEPARATOR);
            if (PHP_OS_FAMILY === 'Windows') {
                foreach ([$name.'.exe', $name] as $n) {
                    $try = $dir.DIRECTORY_SEPARATOR.$n;
                    if (is_file($try)) {
                        return $try;
                    }
                }
            } else {
                $full = $dir.DIRECTORY_SEPARATOR.$name;
                if (is_file($full) && is_executable($full)) {
                    return $full;
                }
            }
        }

        return null;
    }

    /**
     * @param  list<string>  $excludePrivatePrefixes  Relative to private root, e.g. "backups"
     */
    protected function addDirectoryToZip(ZipArchive $zip, string $absoluteDir, string $zipPrefix, array $excludePrivatePrefixes = []): void
    {
        $absoluteDir = rtrim(realpath($absoluteDir) ?: $absoluteDir, DIRECTORY_SEPARATOR);
        if (! is_dir($absoluteDir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($absoluteDir, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            /** @var \SplFileInfo $file */
            if (! $file->isFile()) {
                continue;
            }
            $full = $file->getPathname();
            $relative = ltrim(str_replace($absoluteDir, '', $full), DIRECTORY_SEPARATOR);
            $relative = str_replace(DIRECTORY_SEPARATOR, '/', $relative);

            foreach ($excludePrivatePrefixes as $prefix) {
                $prefix = trim(str_replace('\\', '/', $prefix), '/');
                if ($prefix !== '' && ($relative === $prefix || str_starts_with($relative, $prefix.'/'))) {
                    continue 2;
                }
            }

            $zipPath = $zipPrefix.'/'.$relative;
            $zip->addFile($full, $zipPath);
        }
    }

    /**
     * @param  list<string>  $skipIfUnderPrivate  Relative segments under private root
     */
    protected function mirrorStorageFromExtract(string $extractedSubdir, string $targetRoot, array $skipIfUnderPrivate = []): void
    {
        if (! is_dir($extractedSubdir)) {
            return;
        }

        if (! is_dir($targetRoot)) {
            File::makeDirectory($targetRoot, 0755, true);
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($extractedSubdir, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }
            $full = $file->getPathname();
            $relative = ltrim(str_replace(rtrim($extractedSubdir, DIRECTORY_SEPARATOR), '', $full), DIRECTORY_SEPARATOR);
            $relative = str_replace(DIRECTORY_SEPARATOR, '/', $relative);

            foreach ($skipIfUnderPrivate as $prefix) {
                $prefix = trim(str_replace('\\', '/', $prefix), '/');
                if ($prefix !== '' && ($relative === $prefix || str_starts_with($relative, $prefix.'/'))) {
                    continue 2;
                }
            }

            $dest = $targetRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
            $destDir = dirname($dest);
            if (! is_dir($destDir)) {
                File::makeDirectory($destDir, 0755, true);
            }
            File::copy($full, $dest);
        }
    }
}
