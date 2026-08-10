<?php

namespace Database\Seeders;

use App\Models\TaxoList;
use App\Models\TaxoType;
use Illuminate\Database\Seeder;

class TaxoListSeeder extends Seeder
{
    public function run(): void
    {
        $categoryTypeId    = TaxoType::where('taxo_type_name', 'Category')->value('id') ?? 2;
        $subcategoryTypeId = TaxoType::where('taxo_type_name', 'Subcategory')->value('id') ?? 3;
        $brandTypeId       = TaxoType::where('taxo_type_name', 'Brand')->value('id') ?? 4;
        $countryTypeId     = TaxoType::where('taxo_type_name', 'Country')->value('id') ?? 5;
        $abvTypeId         = TaxoType::where('taxo_type_name', 'Alcohol Content')->value('id') ?? 6;
        $flavourTypeId     = TaxoType::where('taxo_type_name', 'Flavour Profile')->value('id') ?? 7;

        $items = [
            // ==========================================
            // 1. Categories
            // ==========================================
            [
                'taxonomy_name'   => 'Spirits & Hard Liquor',
                'taxonomy_slug'   => 'spirits-hard-liquor',
                'taxonomy_type'   => $categoryTypeId,
                'taxonomy_sort'   => 1,
                'taxonomy_status' => 'ACTIVE',
            ],
            [
                'taxonomy_name'   => 'Wine Collection',
                'taxonomy_slug'   => 'wine-collection',
                'taxonomy_type'   => $categoryTypeId,
                'taxonomy_sort'   => 2,
                'taxonomy_status' => 'ACTIVE',
            ],
            [
                'taxonomy_name'   => 'Beer & Cider',
                'taxonomy_slug'   => 'beer-cider',
                'taxonomy_type'   => $categoryTypeId,
                'taxonomy_sort'   => 3,
                'taxonomy_status' => 'ACTIVE',
            ],
            [
                'taxonomy_name'   => 'Liqueur & Aperitif',
                'taxonomy_slug'   => 'liqueur-aperitif',
                'taxonomy_type'   => $categoryTypeId,
                'taxonomy_sort'   => 4,
                'taxonomy_status' => 'ACTIVE',
            ],

            // ==========================================
            // 2. Subcategories
            // ==========================================
            [
                'taxonomy_name'   => 'Whisky & Bourbon',
                'taxonomy_slug'   => 'whisky-bourbon',
                'taxonomy_type'   => $subcategoryTypeId,
                'taxonomy_sort'   => 1,
                'taxonomy_status' => 'ACTIVE',
            ],
            [
                'taxonomy_name'   => 'Cognac & Brandy',
                'taxonomy_slug'   => 'cognac-brandy',
                'taxonomy_type'   => $subcategoryTypeId,
                'taxonomy_sort'   => 2,
                'taxonomy_status' => 'ACTIVE',
            ],
            [
                'taxonomy_name'   => 'Vodka',
                'taxonomy_slug'   => 'vodka',
                'taxonomy_type'   => $subcategoryTypeId,
                'taxonomy_sort'   => 3,
                'taxonomy_status' => 'ACTIVE',
            ],
            [
                'taxonomy_name'   => 'Gin',
                'taxonomy_slug'   => 'gin',
                'taxonomy_type'   => $subcategoryTypeId,
                'taxonomy_sort'   => 4,
                'taxonomy_status' => 'ACTIVE',
            ],
            [
                'taxonomy_name'   => 'Tequila & Mezcal',
                'taxonomy_slug'   => 'tequila-mezcal',
                'taxonomy_type'   => $subcategoryTypeId,
                'taxonomy_sort'   => 5,
                'taxonomy_status' => 'ACTIVE',
            ],
            [
                'taxonomy_name'   => 'Rum',
                'taxonomy_slug'   => 'rum',
                'taxonomy_type'   => $subcategoryTypeId,
                'taxonomy_sort'   => 6,
                'taxonomy_status' => 'ACTIVE',
            ],
            [
                'taxonomy_name'   => 'Sparkling & Champagne',
                'taxonomy_slug'   => 'sparkling-champagne',
                'taxonomy_type'   => $subcategoryTypeId,
                'taxonomy_sort'   => 7,
                'taxonomy_status' => 'ACTIVE',
            ],
            [
                'taxonomy_name'   => 'Craft Beer & Soju',
                'taxonomy_slug'   => 'craft-beer-ale',
                'taxonomy_type'   => $subcategoryTypeId,
                'taxonomy_sort'   => 8,
                'taxonomy_status' => 'ACTIVE',
            ],

            // ==========================================
            // 3. Brands
            // ==========================================
            [
                'taxonomy_name'   => 'Jägermeister',
                'taxonomy_slug'   => 'jagermeister',
                'taxonomy_type'   => $brandTypeId,
                'taxonomy_sort'   => 1,
                'taxonomy_status' => 'ACTIVE',
            ],
            [
                'taxonomy_name'   => 'Glenfiddich',
                'taxonomy_slug'   => 'glenfiddich',
                'taxonomy_type'   => $brandTypeId,
                'taxonomy_sort'   => 2,
                'taxonomy_status' => 'ACTIVE',
            ],
            [
                'taxonomy_name'   => 'Hennessy',
                'taxonomy_slug'   => 'hennessy',
                'taxonomy_type'   => $brandTypeId,
                'taxonomy_sort'   => 3,
                'taxonomy_status' => 'ACTIVE',
            ],
            [
                'taxonomy_name'   => 'Absolut Vodka',
                'taxonomy_slug'   => 'absolut-vodka',
                'taxonomy_type'   => $brandTypeId,
                'taxonomy_sort'   => 4,
                'taxonomy_status' => 'ACTIVE',
            ],
            [
                'taxonomy_name'   => 'Chum Churum',
                'taxonomy_slug'   => 'chum-churum',
                'taxonomy_type'   => $brandTypeId,
                'taxonomy_sort'   => 5,
                'taxonomy_status' => 'ACTIVE',
            ],
            [
                'taxonomy_name'   => 'Moët & Chandon',
                'taxonomy_slug'   => 'moet-chandon',
                'taxonomy_type'   => $brandTypeId,
                'taxonomy_sort'   => 6,
                'taxonomy_status' => 'ACTIVE',
            ],

            // ==========================================
            // 4. Country of Origin
            // ==========================================
            [
                'taxonomy_name'   => 'Scotland',
                'taxonomy_slug'   => 'scotland',
                'taxonomy_type'   => $countryTypeId,
                'taxonomy_sort'   => 1,
                'taxonomy_status' => 'ACTIVE',
            ],
            [
                'taxonomy_name'   => 'France',
                'taxonomy_slug'   => 'france',
                'taxonomy_type'   => $countryTypeId,
                'taxonomy_sort'   => 2,
                'taxonomy_status' => 'ACTIVE',
            ],
            [
                'taxonomy_name'   => 'Germany',
                'taxonomy_slug'   => 'germany',
                'taxonomy_type'   => $countryTypeId,
                'taxonomy_sort'   => 3,
                'taxonomy_status' => 'ACTIVE',
            ],
            [
                'taxonomy_name'   => 'Sweden',
                'taxonomy_slug'   => 'sweden',
                'taxonomy_type'   => $countryTypeId,
                'taxonomy_sort'   => 4,
                'taxonomy_status' => 'ACTIVE',
            ],
            [
                'taxonomy_name'   => 'South Korea',
                'taxonomy_slug'   => 'south-korea',
                'taxonomy_type'   => $countryTypeId,
                'taxonomy_sort'   => 5,
                'taxonomy_status' => 'ACTIVE',
            ],

            // ==========================================
            // 5. Alcohol Content (ABV Range)
            // ==========================================
            [
                'taxonomy_name'   => 'Low Alcohol (< 15%)',
                'taxonomy_slug'   => 'low-alcohol',
                'taxonomy_type'   => $abvTypeId,
                'taxonomy_sort'   => 1,
                'taxonomy_status' => 'ACTIVE',
            ],
            [
                'taxonomy_name'   => 'Medium Alcohol (15% - 30%)',
                'taxonomy_slug'   => 'medium-alcohol',
                'taxonomy_type'   => $abvTypeId,
                'taxonomy_sort'   => 2,
                'taxonomy_status' => 'ACTIVE',
            ],
            [
                'taxonomy_name'   => 'High Alcohol (> 35%)',
                'taxonomy_slug'   => 'high-alcohol',
                'taxonomy_type'   => $abvTypeId,
                'taxonomy_sort'   => 3,
                'taxonomy_status' => 'ACTIVE',
            ],

            // ==========================================
            // 6. Flavour Profile
            // ==========================================
            [
                'taxonomy_name'   => 'Peaty & Smoky',
                'taxonomy_slug'   => 'peaty-smoky',
                'taxonomy_type'   => $flavourTypeId,
                'taxonomy_sort'   => 1,
                'taxonomy_status' => 'ACTIVE',
            ],
            [
                'taxonomy_name'   => 'Fruity & Floral',
                'taxonomy_slug'   => 'fruity-floral',
                'taxonomy_type'   => $flavourTypeId,
                'taxonomy_sort'   => 2,
                'taxonomy_status' => 'ACTIVE',
            ],
            [
                'taxonomy_name'   => 'Herbal & Spiced',
                'taxonomy_slug'   => 'herbal-spiced',
                'taxonomy_type'   => $flavourTypeId,
                'taxonomy_sort'   => 3,
                'taxonomy_status' => 'ACTIVE',
            ],
            [
                'taxonomy_name'   => 'Sweet & Vanilla',
                'taxonomy_slug'   => 'sweet-vanilla',
                'taxonomy_type'   => $flavourTypeId,
                'taxonomy_sort'   => 4,
                'taxonomy_status' => 'ACTIVE',
            ],
        ];

        foreach ($items as $item) {
            TaxoList::firstOrCreate(
                [
                    'taxonomy_slug' => $item['taxonomy_slug'],
                    'taxonomy_type' => $item['taxonomy_type']
                ],
                $item
            );
        }
    }
}