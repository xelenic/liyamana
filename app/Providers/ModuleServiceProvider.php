<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Base service provider for platform modules.
 * Each module's ModuleServiceProvider should extend this class.
 */
abstract class ModuleServiceProvider extends ServiceProvider
{
    protected string $moduleName = '';

    protected string $modulePath = '';

    public function __construct($app, ?string $path = null)
    {
        parent::__construct($app);
        $this->modulePath = $path ?? '';
        $this->moduleName = $this->moduleName ?: basename($path ?? '');
    }

    /**
     * Register module services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap module: load routes, views, etc.
     */
    public function boot(): void
    {
        $this->loadRoutes();
        $this->loadViews();
    }

    protected function loadRoutes(): void
    {
        $routesPath = $this->modulePath . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'web.php';
        if (file_exists($routesPath)) {
            Route::middleware('web')
                ->group($routesPath);
        }
    }

    protected function loadViews(): void
    {
        $viewsPath = $this->modulePath . DIRECTORY_SEPARATOR . 'views';
        if (is_dir($viewsPath)) {
            View::addNamespace($this->moduleName, $viewsPath);
        }
    }
}
