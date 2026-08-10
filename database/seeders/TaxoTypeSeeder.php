<?php

namespace Database\Seeders;

use App\Models\TaxoType;
use Illuminate\Database\Seeder;

class TaxoTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxoTypes = [
            [
                'taxo_type_name' => 'Collection',
                'taxo_type_description' => 'Product collections, bundles, and special offers',
            ],
            [
                'taxo_type_name' => 'Category',
                'taxo_type_description' => 'Main liquor categories (Spirits, Wine, Beer, Liqueur)',
            ],
            [
                'taxo_type_name' => 'Subcategory',
                'taxo_type_description' => 'Specific beverage types (Whisky, Vodka, Cognac, Champagne)',
            ],
            [
                'taxo_type_name' => 'Brand',
                'taxo_type_description' => 'Beverage distillery and brewery brands',
            ],
            [
                'taxo_type_name' => 'Country',
                'taxo_type_description' => 'Country of origin or region',
            ],
            [
                'taxo_type_name' => 'Alcohol Content',
                'taxo_type_description' => 'Alcohol by volume percentage (ABV Level)',
            ],
            [
                'taxo_type_name' => 'Flavour Profile',
                'taxo_type_description' => 'Tasting notes and flavour profiles (Fruity, Smoky, Herbal)',
            ],
            [
                'taxo_type_name' => 'Specification',
                'taxo_type_description' => 'Bottle dimensions, vintage year, and packaging specs',
            ],
        ];

        foreach ($taxoTypes as $taxoType) {
            TaxoType::firstOrCreate(
                ['taxo_type_name' => $taxoType['taxo_type_name']],
                $taxoType
            );
        }
    }
}