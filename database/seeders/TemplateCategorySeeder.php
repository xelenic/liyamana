<?php

namespace Database\Seeders;

use App\Models\TemplateCategory;
use Illuminate\Database\Seeder;

class TemplateCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Brochure', 'slug' => 'brochure', 'description' => 'Brochures and pamphlets for marketing and information.', 'sort_order' => 1],
            ['name' => 'Business', 'slug' => 'business', 'description' => 'Business documents, reports, and presentations.', 'sort_order' => 2],
            ['name' => 'Letter', 'slug' => 'letter', 'description' => 'Letters and correspondence templates.', 'sort_order' => 3],
            ['name' => 'Visiting Cards', 'slug' => 'visiting-cards', 'description' => 'Business cards and visiting cards.', 'sort_order' => 4],
            ['name' => 'Catalog', 'slug' => 'catalog', 'description' => 'Product catalogs and lookbooks.', 'sort_order' => 5],
            ['name' => 'Magazine', 'slug' => 'magazine', 'description' => 'Magazine and editorial layouts.', 'sort_order' => 6],
            ['name' => 'Portfolio', 'slug' => 'portfolio', 'description' => 'Portfolios and showcase designs.', 'sort_order' => 7],
            ['name' => 'Education', 'slug' => 'education', 'description' => 'Educational materials and worksheets.', 'sort_order' => 8],
            ['name' => 'General', 'slug' => 'general', 'description' => 'General purpose templates.', 'sort_order' => 9],
        ];

        foreach ($categories as $item) {
            TemplateCategory::updateOrCreate(
                ['slug' => $item['slug']],
                array_merge($item, ['is_active' => true])
            );
        }
    }
}
