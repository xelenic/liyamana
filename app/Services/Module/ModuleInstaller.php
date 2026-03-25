<?php

namespace App\Services\Module;

use App\Models\Module;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use ZipArchive;

class ModuleInstaller
{
    protected const MAX_ZIP_SIZE = 10 * 1024 * 1024; // 10MB
    protected const REQUIRED_FILES = ['module.json'];

    public function __construct(
        protected ModuleRegistry $registry
    ) {}

    /**
     * Install a module from an uploaded ZIP file.
     *
     * @throws \InvalidArgumentException
     */
    public function install(UploadedFile $file): Module
    {
        $this->validateZip($file);

        $zip = new ZipArchive();
        if ($zip->open($file->getRealPath()) !== true) {
            throw new \InvalidArgumentException('Invalid or corrupted ZIP file.');
        }

        [$manifest, $zipRoot] = $this->extractAndValidateManifest($zip);
        $moduleName = $manifest['name'];

        if (Module::where('name', $moduleName)->exists()) {
            $zip->close();
            throw new \InvalidArgumentException("Module '{$moduleName}' is already installed.");
        }

        $targetPath = base_path('modules' . DIRECTORY_SEPARATOR . $moduleName);
        $this->ensureModulesDirectoryExists();

        $this->extractZip($zip, $targetPath, $zipRoot);
        $zip->close();

        if (file_exists($targetPath . DIRECTORY_SEPARATOR . 'composer.json')) {
            $this->runComposerDumpAutoload($targetPath);
        }

        $this->runModuleMigrations($targetPath);

        $module = Module::create([
            'name' => $moduleName,
            'version' => $manifest['version'] ?? '1.0.0',
            'enabled' => true,
            'path' => $targetPath,
            'manifest' => $manifest,
            'installed_at' => now(),
        ]);

        $this->registry->clearCache();

        return $module;
    }

    /**
     * Uninstall a module by name.
     */
    public function uninstall(string $moduleName): bool
    {
        $module = Module::where('name', $moduleName)->first();
        if (! $module) {
            return false;
        }

        $path = $module->path;
        if ($path && is_dir($path)) {
            File::deleteDirectory($path);
        }

        $module->delete();
        $this->registry->clearCache();

        return true;
    }

    protected function validateZip(UploadedFile $file): void
    {
        if ($file->getSize() > self::MAX_ZIP_SIZE) {
            throw new \InvalidArgumentException(
                'Module ZIP exceeds maximum size of ' . (self::MAX_ZIP_SIZE / 1024 / 1024) . 'MB.'
            );
        }

        $mime = $file->getMimeType();
        $allowed = ['application/zip', 'application/x-zip-compressed'];
        if (! in_array($mime, $allowed) && $file->getClientOriginalExtension() !== 'zip') {
            throw new \InvalidArgumentException('File must be a valid ZIP archive.');
        }
    }

    /**
     * @return array{0: array<string, mixed>, 1: string}
     */
    protected function extractAndValidateManifest(ZipArchive $zip): array
    {
        $manifestPath = null;
        $rootDir = null;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);
            if ($entry === false) {
                continue;
            }
            // Prevent path traversal
            if (str_contains($entry, '..') || str_starts_with($entry, '/')) {
                throw new \InvalidArgumentException('Invalid path in ZIP archive.');
            }
            $normalized = str_replace('\\', '/', trim($entry, '/'));
            if (! str_ends_with($normalized, 'module.json')) {
                continue;
            }
            $manifestPath = $entry;
            $rootDir = '';
            if (str_contains($normalized, '/')) {
                $rootDir = substr($normalized, 0, strrpos($normalized, '/'));
            }
            break;
        }

        if ($manifestPath === null) {
            throw new \InvalidArgumentException('ZIP must contain module.json in the root or a single top-level directory.');
        }

        $content = $zip->getFromName($manifestPath);
        if ($content === false) {
            throw new \InvalidArgumentException('Could not read module.json from ZIP.');
        }

        $manifest = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('module.json is not valid JSON.');
        }
        if (empty($manifest['name'])) {
            throw new \InvalidArgumentException('module.json must contain a "name" field.');
        }

        return [$manifest, $rootDir];
    }

    protected function extractZip(ZipArchive $zip, string $targetPath, string $zipRoot): void
    {
        $stripRoot = $zipRoot ? $zipRoot . '/' : '';

        File::ensureDirectoryExists($targetPath);

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);
            if ($entry === false) {
                continue;
            }
            if (str_contains($entry, '..') || str_starts_with($entry, '/')) {
                continue;
            }
            $normalized = str_replace('\\', '/', $entry);
            $relativePath = $stripRoot && str_starts_with($normalized, $stripRoot)
                ? substr($normalized, strlen($stripRoot))
                : $normalized;
            $relativePath = rtrim($relativePath, '/');
            if ($relativePath === '') {
                continue;
            }
            $destPath = $targetPath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            if (str_ends_with($entry, '/')) {
                File::ensureDirectoryExists($destPath);
            } else {
                File::ensureDirectoryExists(dirname($destPath));
                $content = $zip->getFromIndex($i);
                if ($content !== false) {
                    file_put_contents($destPath, $content);
                }
            }
        }
    }

    protected function ensureModulesDirectoryExists(): void
    {
        $path = base_path('modules');
        if (! is_dir($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }

    protected function runComposerDumpAutoload(string $modulePath): void
    {
        $basePath = base_path();
        $composerPath = $basePath . '/composer.json';
        if (! file_exists($composerPath)) {
            return;
        }
        $composer = json_decode(file_get_contents($composerPath), true);
        if (! is_array($composer)) {
            return;
        }
        $autoload = $composer['autoload'] ?? [];
        $psr4 = $autoload['psr-4'] ?? [];
        $moduleName = basename($modulePath);
        $namespace = 'Modules\\' . $this->pascalCase($moduleName) . '\\';
        $srcPath = 'modules/' . $moduleName . '/src/';
        if (! isset($psr4[$namespace]) && is_dir($modulePath . '/src')) {
            $psr4[$namespace] = $srcPath;
            $autoload['psr-4'] = $psr4;
            $composer['autoload'] = $autoload;
            file_put_contents($composerPath, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
        exec('cd ' . escapeshellarg($basePath) . ' && composer dump-autoload 2>/dev/null', $out, $code);
    }

    protected function pascalCase(string $str): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $str)));
    }

    protected function runModuleMigrations(string $modulePath): void
    {
        $migrationsPath = $modulePath . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations';
        if (! is_dir($migrationsPath)) {
            return;
        }

        Artisan::call('migrate', [
            '--path' => 'modules/' . basename($modulePath) . '/database/migrations',
            '--force' => true,
        ]);
    }
}
