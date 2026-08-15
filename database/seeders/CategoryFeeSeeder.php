<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategoryMaster;

class CategoryFeeSeeder extends Seeder
{
    /**
     * Seed or update registration categories with their exact base fees.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'RAINBOW 3',
                'code' => 'RB3',
                'registration_fee' => 150.00,
                'status' => true,
            ],
            [
                'name' => 'RAINBOW 4',
                'code' => 'RB4',
                'registration_fee' => 150.00,
                'status' => true,
            ],
            [
                'name' => 'RAINBOW 5',
                'code' => 'RB5',
                'registration_fee' => 150.00,
                'status' => true,
            ],
            [
                'name' => 'PLANET',
                'code' => 'PLN',
                'registration_fee' => 250.00,
                'status' => true,
            ],
            [
                'name' => 'GALAXY HS',
                'code' => 'GLX_HS',
                'registration_fee' => 250.00,
                'status' => true,
            ],
            [
                'name' => 'GALAXY HSS (ARTS)',
                'code' => 'GLX_ARTS',
                'registration_fee' => 250.00,
                'status' => true,
            ],
            [
                'name' => 'GALAXY HSS (SCIENCE)',
                'code' => 'GLX_SCI',
                'registration_fee' => 250.00,
                'status' => true,
            ],
        ];

        foreach ($categories as $cat) {
            CategoryMaster::updateOrCreate(
                ['name' => $cat['name']],
                [
                    'code' => $cat['code'],
                    'registration_fee' => $cat['registration_fee'],
                    'status' => $cat['status'],
                ]
            );
        }
    }
}
