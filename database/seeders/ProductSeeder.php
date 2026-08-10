<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use App\Models\ProductCategory;
use App\Models\ProductVariantStock;
use App\Models\ProductVariantOption;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Store;
use App\Models\TaxoList;
use App\Models\TaxoType;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultStore = Store::firstOrCreate(
            ['code' => 'STORE-001'],
            [
                'name' => 'Main Store',
                'code' => 'STORE-001',
                'status' => 'ACTIVE',
            ]
        );

        $categoryTypeId = TaxoType::where('taxo_type_name', 'Category')->value('id');
        $subcategoryTypeId = TaxoType::where('taxo_type_name', 'Subcategory')->value('id');

        $attachProductTaxonomy = function (Product $product, string $slug, ?int $taxonomyTypeId): void {
            if (!$taxonomyTypeId) {
                return;
            }

            $taxonomy = TaxoList::where('taxonomy_slug', $slug)
                ->where('taxonomy_type', $taxonomyTypeId)
                ->first();

            if ($taxonomy) {
                ProductCategory::firstOrCreate([
                    'fk_product_id' => $product->id,
                    'fk_category_id' => $taxonomy->id,
                ]);
            }
        };

        $discountPercent = fn(int $price, int $strikePrice): float => round((($strikePrice - $price) / $strikePrice) * 100, 2);

        $syncProductPricing = function (Product $product, int $price, int $strikePrice) use ($discountPercent): void {
            $product->update([
                'base_price' => $price,
                'base_strike_price' => $strikePrice,
                'base_discount_percent' => $discountPercent($price, $strikePrice),
            ]);
        };

        $syncVariantPricing = function (ProductVariant $variant, int $price, int $strikePrice) use ($discountPercent): void {
            $variant->update([
                'price' => $price,
                'strike_price' => $strikePrice,
                'discount_percent' => $discountPercent($price, $strikePrice),
            ]);
        };

        // Attributes untuk Minuman
        $ukuranAttribute = Attribute::firstOrCreate(
            ['slug' => 'ukuran-botol'],
            ['name' => 'Ukuran Botol', 'slug' => 'ukuran-botol', 'sort' => 1, 'status' => 'ACTIVE']
        );

        $v350ml = AttributeValue::firstOrCreate(
            ['attribute_id' => $ukuranAttribute->id, 'slug' => '350ml'],
            ['attribute_id' => $ukuranAttribute->id, 'value' => '350ml', 'slug' => '350ml', 'sort' => 1, 'status' => 'ACTIVE']
        );
        $v700ml = AttributeValue::firstOrCreate(
            ['attribute_id' => $ukuranAttribute->id, 'slug' => '700ml'],
            ['attribute_id' => $ukuranAttribute->id, 'value' => '700ml', 'slug' => '700ml', 'sort' => 2, 'status' => 'ACTIVE']
        );
        $v750ml = AttributeValue::firstOrCreate(
            ['attribute_id' => $ukuranAttribute->id, 'slug' => '750ml'],
            ['attribute_id' => $ukuranAttribute->id, 'value' => '750ml', 'slug' => '750ml', 'sort' => 3, 'status' => 'ACTIVE']
        );
        $v1000ml = AttributeValue::firstOrCreate(
            ['attribute_id' => $ukuranAttribute->id, 'slug' => '1000ml'],
            ['attribute_id' => $ukuranAttribute->id, 'value' => '1 Liter', 'slug' => '1000ml', 'sort' => 4, 'status' => 'ACTIVE']
        );

        // List Produk Realistis Minuman Murah
        $productsData = [
            [
                'slug' => 'jagermeister-herbal-liqueur',
                'name' => 'Jagermeister Herbal Liqueur',
                'info' => 'Minuman herbal liqueur khas Jerman racikan 56 rempah pilihan. Cocok disajikan super dingin (ice cold shot).',
                'category_slug' => 'spirits-hard-liquor',
                'subcategory_slug' => 'cognac-brandy', // Boleh disesuaikan
                'base_price' => 365000,
                'base_strike' => 420000,
                'weight' => 1.2,
                'tags' => 'jagermeister,liqueur,herbal,spirits',
                'image_text' => 'Jagermeister',
                'variants' => [
                    ['name' => '700ml', 'sku' => 'JAGER-700ML', 'price' => 365000, 'strike' => 420000, 'attr_val' => $v700ml, 'qty' => 50],
                    ['name' => '1 Liter', 'sku' => 'JAGER-1000ML', 'price' => 485000, 'strike' => 550000, 'attr_val' => $v1000ml, 'qty' => 30],
                ]
            ],
            [
                'slug' => 'glenfiddich-12-years-single-malt-whisky',
                'name' => 'Glenfiddich 12 Years Single Malt Whisky',
                'slug' => 'glenfiddich-12-years',
                'info' => 'Single Malt Scotch Whisky terpopuler dengan aroma buah pir segar dan rasa kayu oak manis yang halus.',
                'category_slug' => 'spirits-hard-liquor',
                'subcategory_slug' => 'whisky-bourbon',
                'base_price' => 725000,
                'base_strike' => 850000,
                'weight' => 1.4,
                'tags' => 'whisky,glenfiddich,single malt,scotch',
                'image_text' => 'Glenfiddich+12',
                'variants' => [
                    ['name' => '700ml', 'sku' => 'GLEN-12-700ML', 'price' => 725000, 'strike' => 850000, 'attr_val' => $v700ml, 'qty' => 25],
                ]
            ],
            [
                'slug' => 'hennessy-xo-cognac-700ml',
                'name' => 'Hennessy XO Cognac 700ml',
                'info' => 'Cognac super premium asal Prancis dengan keharuman kaya akan buah-buahan kering, rempah, dan aroma kayu tua.',
                'category_slug' => 'spirits-hard-liquor',
                'subcategory_slug' => 'cognac-brandy',
                'base_price' => 3145000,
                'base_strike' => 3500000,
                'weight' => 1.6,
                'tags' => 'hennessy,cognac,xo,french cognac',
                'image_text' => 'Hennessy+XO',
                'variants' => [
                    ['name' => '700ml', 'sku' => 'HENNESSY-XO-700ML', 'price' => 3145000, 'strike' => 3500000, 'attr_val' => $v700ml, 'qty' => 10],
                ]
            ],
            [
                'slug' => 'absolut-vodka-750ml',
                'name' => 'Absolut Vodka 750ml',
                'info' => 'Vodka ikonis dari Swedia berbahan dasar gandum gandum murni tanpa gula tambahan.',
                'category_slug' => 'spirits-hard-liquor',
                'subcategory_slug' => 'vodka',
                'base_price' => 310000,
                'base_strike' => 360000,
                'weight' => 1.3,
                'tags' => 'vodka,absolut,sweden,spirits',
                'image_text' => 'Absolut+Vodka',
                'variants' => [
                    ['name' => '750ml', 'sku' => 'ABSOLUT-VODKA-750ML', 'price' => 310000, 'strike' => 360000, 'attr_val' => $v750ml, 'qty' => 40],
                ]
            ],
            [
                'slug' => 'chum-churum-original-soju',
                'name' => 'Chum Churum Original Soju',
                'info' => 'Minuman Soju khas Korea Selatan dengan tekstur lembut yang diolah menggunakan air alkalin alami.',
                'category_slug' => 'beer-cider',
                'subcategory_slug' => 'craft-beer-ale',
                'base_price' => 45000,
                'base_strike' => 55000,
                'weight' => 0.8,
                'tags' => 'soju,chum churum,korea,soju murah',
                'image_text' => 'Chum+Churum+Soju',
                'variants' => [
                    ['name' => '360ml', 'sku' => 'SOJU-CHUM-360ML', 'price' => 45000, 'strike' => 55000, 'attr_val' => $v350ml, 'qty' => 100],
                ]
            ],
            [
                'slug' => 'moet-chandon-imp-rial-brut-champagne',
                'name' => 'Moet & Chandon Impérial Brut Champagne',
                'info' => 'Champagne ikonis Prancis yang kaya rasa apel hijau, buah sitrus, serta hint kacang hazelnut yang segar.',
                'category_slug' => 'wine-collection',
                'subcategory_slug' => 'sparkling-champagne',
                'base_price' => 1150000,
                'base_strike' => 1350000,
                'weight' => 1.5,
                'tags' => 'champagne,moet,wine,sparkling',
                'image_text' => 'Moet+Chandon',
                'variants' => [
                    ['name' => '750ml', 'sku' => 'MOET-BRUT-750ML', 'price' => 1150000, 'strike' => 1350000, 'attr_val' => $v750ml, 'qty' => 15],
                ]
            ],
        ];

        foreach ($productsData as $i => $p) {
            $product = Product::firstOrCreate(
                ['slug' => $p['slug']],
                [
                    'name' => $p['name'],
                    'slug' => $p['slug'],
                    'is_freeshiping' => 'ACTIVE',
                    'product_information' => $p['info'],
                    'meta_keywords' => $p['tags'],
                    'meta_description' => $p['info'],
                    'meta_title' => $p['name'] . ' - Minuman Murah',
                    'weight' => $p['weight'],
                    'type_weight' => 'KG',
                    'size_long' => 10,
                    'size_tall' => 32,
                    'size_wide' => 10,
                    'type_size' => 'CM',
                    'sort' => $i + 1,
                    'tags' => $p['tags'],
                    'status' => 'PUBLISH',
                    'base_price' => $p['base_price'],
                    'base_strike_price' => $p['base_strike'],
                    'base_discount_percent' => $discountPercent($p['base_price'], $p['base_strike']),
                ]
            );

            $syncProductPricing($product, $p['base_price'], $p['base_strike']);

            $attachProductTaxonomy($product, $p['category_slug'], $categoryTypeId);
            $attachProductTaxonomy($product, $p['subcategory_slug'], $subcategoryTypeId);

            $productUkuranAttr = ProductAttribute::firstOrCreate(
                ['product_id' => $product->id, 'attribute_id' => $ukuranAttribute->id],
                ['sort' => 1]
            );

            foreach ($p['variants'] as $v) {
                ProductAttributeValue::firstOrCreate([
                    'product_attribute_id' => $productUkuranAttr->id,
                    'attribute_value_id' => $v['attr_val']->id,
                ]);

                $variant = ProductVariant::firstOrCreate(
                    ['fk_product_id' => $product->id, 'sku' => $v['sku']],
                    [
                        'variant_name' => $v['name'],
                        'sku' => $v['sku'],
                        'price' => $v['price'],
                        'strike_price' => $v['strike'],
                        'discount_percent' => $discountPercent($v['price'], $v['strike']),
                        'status' => 'ACTIVE',
                        'image_path' => 'https://via.placeholder.com/600x800/1c1c1c/ffffff?text=' . $p['image_text']
                    ]
                );

                $syncVariantPricing($variant, $v['price'], $v['strike']);

                ProductVariantOption::firstOrCreate([
                    'variant_id' => $variant->id,
                    'attribute_id' => $ukuranAttribute->id,
                    'attribute_value_id' => $v['attr_val']->id
                ]);

                ProductVariantStock::firstOrCreate(
                    ['variant_id' => $variant->id, 'store_id' => $defaultStore->id],
                    ['qty' => $v['qty'], 'reserved_qty' => 0]
                );
            }

            ProductImage::firstOrCreate(
                ['fk_product_id' => $product->id, 'order_number' => 1],
                [
                    'path' => 'https://via.placeholder.com/600x800/1c1c1c/ffffff?text=' . $p['image_text'],
                    'order_number' => 1,
                    'is_featured' => true
                ]
            );
        }
    }
}