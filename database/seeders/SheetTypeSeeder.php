<?php

namespace Database\Seeders;

use App\Models\SheetType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SheetTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sheetTypes = [
            [
                'name' => 'Standard',
                'slug' => 'standard',
                'multiplier' => 1.0,
                'price_per_sheet' => 0.50,
                'description' => 'Standard paper quality',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Glossy',
                'slug' => 'glossy',
                'multiplier' => 1.2,
                'price_per_sheet' => 0.60,
                'description' => 'Glossy finish paper',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Matte',
                'slug' => 'matte',
                'multiplier' => 1.15,
                'price_per_sheet' => 0.58,
                'description' => 'Matte finish paper',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Satin',
                'slug' => 'satin',
                'multiplier' => 1.25,
                'price_per_sheet' => 0.63,
                'description' => 'Satin finish paper',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Textured',
                'slug' => 'textured',
                'multiplier' => 1.3,
                'price_per_sheet' => 0.65,
                'description' => 'Textured finish paper',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($sheetTypes as $sheetType) {
            SheetType::updateOrCreate(
                ['slug' => $sheetType['slug']],
                $sheetType
            );
        }
    }
}
