<?php

namespace Database\Seeders;

use App\Models\EnvelopeType;
use Illuminate\Database\Seeder;

class EnvelopeTypeSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'Standard White', 'slug' => 'standard', 'price_per_letter' => 0, 'sort_order' => 0],
            ['name' => 'Premium Cream', 'slug' => 'premium_cream', 'price_per_letter' => 0.15, 'sort_order' => 10],
            ['name' => 'Window Envelope', 'slug' => 'window', 'price_per_letter' => 0.10, 'sort_order' => 20],
            ['name' => 'Kraft Brown', 'slug' => 'kraft', 'price_per_letter' => 0.08, 'sort_order' => 30],
            ['name' => 'Linen Texture', 'slug' => 'linen', 'price_per_letter' => 0.25, 'sort_order' => 40],
        ];

        foreach ($rows as $row) {
            EnvelopeType::updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    'price_per_letter' => $row['price_per_letter'],
                    'sort_order' => $row['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
