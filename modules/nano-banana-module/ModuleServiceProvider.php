<?php

namespace Modules\NanoBananaModule;

use App\Providers\ModuleServiceProvider as BaseModuleServiceProvider;
use Illuminate\Support\Facades\View;
use Modules\NanoBananaModule\Models\NanoBananaTemplate;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected string $moduleName = 'nano-banana-module';

    public function boot(): void
    {
        parent::boot();

        View::composer('design.templates.explore', function ($view) {
            $costPerGenerate = (float) \App\Models\Setting::get('gemini_image_cost', 1);
            $templates = NanoBananaTemplate::active()->ordered()->get()->map(function ($t) use ($costPerGenerate) {
                return [
                    'id' => 'nb-' . $t->id,
                    'nanobanana_id' => $t->id,
                    'name' => $t->name,
                    'short_description' => $t->description ? \Str::limit($t->description, 80) : 'AI-generated design with Gemini',
                    'description' => $t->description,
                    'category' => 'AI Generated',
                    'type' => 'nanobanana',
                    'price' => $costPerGenerate,
                    'cost_per_generate' => $costPerGenerate,
                    'thumbnail_url' => $t->thumbnail_url,
                    'thumbnail_path' => $t->image_path,
                    'page_count' => 1,
                    'is_nanobanana' => true,
                ];
            })->toArray();
            $view->with('nanoBananaTemplates', $templates);
        });
    }
}
