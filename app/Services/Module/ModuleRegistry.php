<?php

namespace App\Services\Module;

use App\Models\Module;
use Illuminate\Support\Facades\Cache;

class ModuleRegistry
{
    protected const CACHE_KEY = 'module_registry';
    protected const CACHE_TTL = 3600; // 1 hour

    /**
     * Get all installed modules from the database.
     */
    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return Module::orderBy('name')->get();
    }

    /**
     * Get all enabled modules.
     */
    public function getEnabled(): \Illuminate\Database\Eloquent\Collection
    {
        return Module::where('enabled', true)->orderBy('name')->get();
    }

    /**
     * Get editor elements from all enabled modules.
     * Cached for performance.
     *
     * @return array<int, array{id: string, label: string, icon: string, description: string, handler: string, panel: string, module: string}>
     */
    public function getEditorElements(): array
    {
        return Cache::remember(
            self::CACHE_KEY . '_editor_elements',
            self::CACHE_TTL,
            fn () => $this->collectEditorElements()
        );
    }

    /**
     * Get admin menu items from all enabled modules.
     * Cached for performance.
     *
     * @return array<int, array{label: string, icon: string, route: string, parent: string|null, module: string}>
     */
    public function getAdminMenuItems(): array
    {
        return Cache::remember(
            self::CACHE_KEY . '_admin_menu',
            self::CACHE_TTL,
            fn () => $this->collectAdminMenuItems()
        );
    }

    /**
     * Get image properties panels from enabled modules.
     * These panels appear in the right sidebar when an image is selected.
     *
     * @return array<int, array{module: string, view: string, label: string, icon: string}>
     */
    public function getImagePropertiesPanels(): array
    {
        return Cache::remember(
            self::CACHE_KEY . '_image_properties_panels',
            self::CACHE_TTL,
            fn () => $this->collectImagePropertiesPanels()
        );
    }

    /**
     * Clear the module registry cache.
     * Call when modules are enabled, disabled, installed, or uninstalled.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY . '_editor_elements');
        Cache::forget(self::CACHE_KEY . '_admin_menu');
        Cache::forget(self::CACHE_KEY . '_image_properties_panels');
    }

    /**
     * Scan modules directory for module.json manifests.
     * Returns modules that exist on disk but may not be in DB.
     *
     * @return array<string, array{name: string, path: string, manifest: array}>
     */
    public function scanModulesDirectory(): array
    {
        $modulesPath = base_path('modules');
        $discovered = [];

        if (! is_dir($modulesPath)) {
            return $discovered;
        }

        $dirs = scandir($modulesPath);
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }
            $path = $modulesPath . DIRECTORY_SEPARATOR . $dir;
            if (! is_dir($path)) {
                continue;
            }
            $manifestPath = $path . DIRECTORY_SEPARATOR . 'module.json';
            if (! file_exists($manifestPath)) {
                continue;
            }
            $content = file_get_contents($manifestPath);
            $manifest = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE || ! isset($manifest['name'])) {
                continue;
            }
            $discovered[$manifest['name']] = [
                'name' => $manifest['name'],
                'path' => $path,
                'manifest' => $manifest,
            ];
        }

        return $discovered;
    }

    protected function collectEditorElements(): array
    {
        $elements = [];
        foreach ($this->getEnabled() as $module) {
            $manifest = $module->manifest ?? [];
            $editorElements = $manifest['editor_elements'] ?? [];
            foreach ($editorElements as $el) {
                if (isset($el['id'], $el['label'], $el['handler'])) {
                    $elements[] = array_merge($el, [
                        'module' => $module->name,
                        'icon' => $el['icon'] ?? 'fa-cube',
                        'description' => $el['description'] ?? '',
                        'panel' => $el['panel'] ?? 'elements',
                    ]);
                }
            }
        }
        return $elements;
    }

    protected function collectAdminMenuItems(): array
    {
        $items = [];
        foreach ($this->getEnabled() as $module) {
            $manifest = $module->manifest ?? [];
            $adminMenu = $manifest['admin_menu'] ?? null;
            if (is_array($adminMenu) && isset($adminMenu['label'], $adminMenu['route'])) {
                $items[] = [
                    'label' => $adminMenu['label'],
                    'icon' => $adminMenu['icon'] ?? 'fa-cube',
                    'route' => $adminMenu['route'],
                    'parent' => $adminMenu['parent'] ?? null,
                    'module' => $module->name,
                ];
            }
        }
        return $items;
    }

    protected function collectImagePropertiesPanels(): array
    {
        $panels = [];
        foreach ($this->getEnabled() as $module) {
            $manifest = $module->manifest ?? [];
            $panel = $manifest['image_properties_panel'] ?? null;
            if (is_array($panel) && isset($panel['view'])) {
                $panels[] = [
                    'module' => $module->name,
                    'view' => $panel['view'],
                    'label' => $panel['label'] ?? 'Image Color',
                    'icon' => $panel['icon'] ?? 'fa-adjust',
                ];
            }
        }
        return $panels;
    }
}
