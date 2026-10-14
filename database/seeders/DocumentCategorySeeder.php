<?php

namespace Database\Seeders;

use App\Models\DocumentCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DocumentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Vehicle',
                'icon' => 'bi-car-front-fill',
                'color' => '#0d6efd',
            ],
            [
                'name' => 'Insurance',
                'icon' => 'bi-shield-shaded',
                'color' => '#198754',
            ],
            [
                'name' => 'Identity & Travel',
                'icon' => 'bi-passport-fill',
                'color' => '#6f42c1',
            ],
            [
                'name' => 'Appliance Warranty',
                'icon' => 'bi-tv-fill',
                'color' => '#fd7e14',
            ],
            [
                'name' => 'Property & Housing',
                'icon' => 'bi-house-door-fill',
                'color' => '#20c997',
            ],
            [
                'name' => 'Education',
                'icon' => 'bi-mortarboard-fill',
                'color' => '#0dcaf0',
            ],
            [
                'name' => 'Medical & Health',
                'icon' => 'bi-heart-pulse-fill',
                'color' => '#dc3545',
            ],
            [
                'name' => 'Other Documents',
                'icon' => 'bi-folder-fill',
                'color' => '#6c757d',
            ],
        ];

        foreach ($categories as $cat) {
            DocumentCategory::firstOrCreate(
                ['slug' => Str::slug($cat['name']), 'is_system' => true],
                [
                    'family_id' => null,
                    'name' => $cat['name'],
                    'icon' => $cat['icon'],
                    'color' => $cat['color'],
                    'is_system' => true,
                ]
            );
        }
    }
}
