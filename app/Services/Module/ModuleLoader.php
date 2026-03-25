<?php

namespace App\Services\Module;

use Illuminate\Support\Facades\File;

class ModuleLoader
{
    public function __construct(
        protected ModuleRegistry $registry
    ) {}

    /**
     * Bootstrap all enabled modules.
     * Loads each module's ModuleServiceProvider.
     */
    public function bootstrap(): void
    {
        foreach ($this->registry->getEnabled() as $module) {
            $this->loadModule($module);
        }
    }

    /**
     * Load a single module's service provider and register routes, views, etc.
     */
    protected function loadModule(\App\Models\Module $module): void
    {
        $path = $module->path;
        if (! $path || ! is_dir($path)) {
            return;
        }

        $providerPath = $path . DIRECTORY_SEPARATOR . 'ModuleServiceProvider.php';
        if (! file_exists($providerPath)) {
            return;
        }

        $providerClass = $this->resolveProviderClass($module->name);
        if (! $providerClass) {
            return;
        }

        if (! class_exists($providerClass)) {
            require_once $providerPath;
        }

        if (class_exists($providerClass)) {
            $provider = new $providerClass(app(), $path);
            $provider->register();
            $provider->boot();
        }
    }

    /**
     * Resolve the ModuleServiceProvider class name for a module.
     */
    protected function resolveProviderClass(string $moduleName): ?string
    {
        $path = base_path('modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'ModuleServiceProvider.php');
        if (! file_exists($path)) {
            return null;
        }

        $content = file_get_contents($path);
        $namespace = null;
        $className = null;

        if (preg_match('/namespace\s+([^;]+)\s*;/', $content, $m)) {
            $namespace = trim($m[1]);
        }
        if (preg_match('/class\s+(\w+)/', $content, $m2)) {
            $className = $m2[1];
        }

        if ($namespace && $className) {
            return $namespace . '\\' . $className;
        }
        if ($className) {
            return $className;
        }

        return null;
    }
}
