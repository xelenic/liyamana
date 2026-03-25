<?php

namespace Modules\PhotoColorsModule;

use App\Providers\ModuleServiceProvider as BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected string $moduleName = 'photo-colors-module';

    public function boot(): void
    {
        parent::boot();
    }
}
