<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $siteName = config('app.name') ?: 'FlipBook';

        $items = [
            [
                'name' => 'Sarah Mitchell',
                'role' => 'Marketing Director',
                'content' => "{$siteName} has completely transformed how we present our product catalogs. The design tools are intuitive and the results are stunning. Our customers love the interactive experience!",
                'rating' => 5,
                'sort_order' => 1,
            ],
            [
                'name' => 'James Davis',
                'role' => 'Creative Designer',
                'content' => "As a freelance designer, {$siteName} has become an essential tool in my workflow. The templates are professional and the customization options are endless. Highly recommended!",
                'rating' => 5,
                'sort_order' => 2,
            ],
            [
                'name' => 'Michael Roberts',
                'role' => 'Business Owner',
                'content' => "The best investment we've made for our business. Creating flip books used to take days, now it takes minutes. The ROI is incredible!",
                'rating' => 5,
                'sort_order' => 3,
            ],
        ];

        foreach ($items as $item) {
            Testimonial::updateOrCreate(
                ['name' => $item['name'], 'role' => $item['role']],
                array_merge($item, ['is_active' => true])
            );
        }
    }
}
