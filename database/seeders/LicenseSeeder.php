<?php

namespace Database\Seeders;

use App\Models\License;
use Illuminate\Database\Seeder;

class LicenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $licenses = [
            ['name' => 'Standard', 'slug' => 'standard', 'description' => 'Standard license for personal and limited commercial use.', 'sort_order' => 0],
            ['name' => 'Extended', 'slug' => 'extended', 'description' => 'Extended license for broader commercial use.', 'sort_order' => 1],
            ['name' => 'Commercial', 'slug' => 'commercial', 'description' => 'Full commercial license for unlimited use.', 'sort_order' => 2],
        ];

        foreach ($licenses as $license) {
            License::updateOrCreate(
                ['slug' => $license['slug']],
                array_merge($license, ['is_active' => true])
            );
        }
    }
}
