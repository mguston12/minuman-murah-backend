<?php

namespace Database\Seeders;

use App\Models\TaxoList;
use App\Models\TaxoType;
use Illuminate\Database\Seeder;

class CategoryProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryTypeId = TaxoType::where('taxo_type_name', 'Category')->value('id');
        $subcategoryTypeId = TaxoType::where('taxo_type_name', 'Subcategory')->value('id');

        if (!$categoryTypeId || !$subcategoryTypeId) {
            $this->command?->warn('Category/Subcategory taxo types not found. Skipping category seed.');

            return;
        }

        $categories = [
            [
                'name' => 'Spirits & Hard Liquor',
                'slug' => 'spirits-hard-liquor',
                'sort' => 1,
                'subcategories' => [
                    ['name' => 'Whisky & Bourbon', 'slug' => 'whisky-bourbon', 'sort' => 1],
                    ['name' => 'Vodka', 'slug' => 'vodka', 'sort' => 2],
                    ['name' => 'Gin', 'slug' => 'gin', 'sort' => 3],
                    ['name' => 'Rum', 'slug' => 'rum', 'sort' => 4],
                    ['name' => 'Tequila', 'slug' => 'tequila', 'sort' => 5],
                    ['name' => 'Cognac & Brandy', 'slug' => 'cognac-brandy', 'sort' => 6],
                ],
            ],
            [
                'name' => 'Wine Collection',
                'slug' => 'wine-collection',
                'sort' => 2,
                'subcategories' => [
                    ['name' => 'Red Wine', 'slug' => 'red-wine', 'sort' => 1],
                    ['name' => 'White Wine', 'slug' => 'white-wine', 'sort' => 2],
                    ['name' => 'Sparkling & Champagne', 'slug' => 'sparkling-champagne', 'sort' => 3],
                    ['name' => 'Rosé Wine', 'slug' => 'rose-wine', 'sort' => 4],
                ],
            ],
            [
                'name' => 'Beer & Cider',
                'slug' => 'beer-cider',
                'sort' => 3,
                'subcategories' => [
                    ['name' => 'Lager & Pilsner', 'slug' => 'lager-pilsner', 'sort' => 1],
                    ['name' => 'Stout & Porter', 'slug' => 'stout-porter', 'sort' => 2],
                    ['name' => 'Craft Beer & Ale', 'slug' => 'craft-beer-ale', 'sort' => 3],
                    ['name' => 'Fruit Cider', 'slug' => 'fruit-cider', 'sort' => 4],
                ],
            ],
            [
                'name' => 'Soft Drinks & Mixer',
                'slug' => 'soft-drinks-mixer',
                'sort' => 4,
                'subcategories' => [
                    ['name' => 'Tonic & Soda Water', 'slug' => 'tonic-soda-water', 'sort' => 1],
                    ['name' => 'Carbonated Drinks', 'slug' => 'carbonated-drinks', 'sort' => 2],
                    ['name' => 'Juice & Syrup', 'slug' => 'juice-syrup', 'sort' => 3],
                    ['name' => 'Energy Drinks', 'slug' => 'energy-drinks', 'sort' => 4],
                ],
            ],
            [
                'name' => 'Bundling & Party Pack',
                'slug' => 'bundling-party-pack',
                'sort' => 5,
                'subcategories' => [
                    ['name' => 'Paket Hemat Bar', 'slug' => 'paket-hemat-bar', 'sort' => 1],
                    ['name' => 'Party Set', 'slug' => 'party-set', 'sort' => 2],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $category = TaxoList::updateOrCreate(
                [
                    'taxonomy_slug' => $categoryData['slug'],
                    'taxonomy_type' => $categoryTypeId,
                ],
                [
                    'parent' => null,
                    'taxonomy_name' => $categoryData['name'],
                    'taxonomy_slug' => $categoryData['slug'],
                    'taxonomy_type' => $categoryTypeId,
                    'taxonomy_sort' => $categoryData['sort'],
                    'taxonomy_status' => 'ACTIVE',
                ]
            );

            foreach ($categoryData['subcategories'] as $subcategoryData) {
                TaxoList::updateOrCreate(
                    [
                        'taxonomy_slug' => $subcategoryData['slug'],
                        'taxonomy_type' => $subcategoryTypeId,
                    ],
                    [
                        'parent' => $category->id,
                        'taxonomy_name' => $subcategoryData['name'],
                        'taxonomy_slug' => $subcategoryData['slug'],
                        'taxonomy_type' => $subcategoryTypeId,
                        'taxonomy_sort' => $subcategoryData['sort'],
                        'taxonomy_status' => 'ACTIVE',
                    ]
                );
            }
        }
    }
}