<?php

namespace Database\Seeders;

use App\Models\ProductGroup;
use App\Models\ProductSubGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductGroupSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $groups = [
            [
                'title' => 'Penawaran Spesial',
                'key' => 'penawaran-spesial',
                'subgroups' => [
                    'Flash Sale 21+',
                    'Diskon Pengguna Baru',
                    'Promo Bundle Party',
                ],
            ],
            [
                'title' => 'Rekomendasi Untukmu',
                'key' => 'rekomendasi-untukmu',
                'subgroups' => [
                    'Produk Terlaris',
                    'Harga Murah',
                    'Top Brands',
                    'New Arrivals',
                ],
            ],
        ];

        foreach ($groups as $groupData) {
            $group = ProductGroup::updateOrCreate(
                ['key' => $groupData['key']],
                ['title' => $groupData['title']]
            );

            foreach ($groupData['subgroups'] as $subTitle) {
                ProductSubGroup::updateOrCreate(
                    [
                        'product_group_id' => $group->id,
                        'title' => $subTitle,
                    ],
                    [
                        'product_group_id' => $group->id,
                        'title' => $subTitle,
                    ]
                );
            }
        }
    }
}