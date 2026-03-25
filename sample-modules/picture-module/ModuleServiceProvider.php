<?php

namespace Modules\PictureModule;

use App\Providers\ModuleServiceProvider as BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected string $moduleName = 'picture-module';

    public function boot(): void
    {
        parent::boot();
    }
}
