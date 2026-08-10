<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $baseUrl = rtrim(config('app.url'), '/');

        $brands = [
            [
                'name' => 'Johnnie Walker',
                'slug' => 'johnnie-walker',
                'logo' => "$baseUrl/uploads/brands/johnnie-walker.png",
                'status' => 'ACTIVE',
                'order' => 1,
                'description' => 'Merek Scotch Whisky paling terkenal di dunia dengan varian ikonik seperti Red Label, Black Label, hingga Blue Label.',
            ],
            [
                'name' => 'Hennessy',
                'slug' => 'hennessy',
                'logo' => "$baseUrl/uploads/brands/hennessy.png",
                'status' => 'ACTIVE',
                'order' => 2,
                'description' => 'Produsen Cognac mewah terkemuka asal Prancis yang terkenal dengan cita rasa halus dan aroma yang kaya.',
            ],
            [
                'name' => 'Absolut Vodka',
                'slug' => 'absolut-vodka',
                'logo' => "$baseUrl/uploads/brands/absolut-vodka.png",
                'status' => 'ACTIVE',
                'order' => 3,
                'description' => 'Merek Vodka premium asal Swedia yang diproduksi dari gandum musim dingin alami tanpa gula tambahan.',
            ],
            [
                'name' => 'Tanqueray',
                'slug' => 'tanqueray',
                'logo' => "$baseUrl/uploads/brands/tanqueray.png",
                'status' => 'ACTIVE',
                'order' => 4,
                'description' => 'London Dry Gin klasik dengan karakter botanical juniper yang kuat, sempurna untuk racikan Gin & Tonic.',
            ],
            [
                'name' => 'Bacardi',
                'slug' => 'bacardi',
                'logo' => "$baseUrl/uploads/brands/bacardi.png",
                'status' => 'ACTIVE',
                'order' => 5,
                'description' => 'Merek Rum legendaris yang ideal untuk berbagai cocktail klasik seperti Mojito dan Cuba Libre.',
            ],
            [
                'name' => 'Heineken',
                'slug' => 'heineken',
                'logo' => "$baseUrl/uploads/brands/heineken.png",
                'status' => 'ACTIVE',
                'order' => 6,
                'description' => 'Beer lager premium asal Belanda dengan rasa segar, ringan, dan warna keemasan yang khas.',
            ],
            [
                'name' => 'Penfolds',
                'slug' => 'penfolds',
                'logo' => "$baseUrl/uploads/brands/penfolds.png",
                'status' => 'ACTIVE',
                'order' => 7,
                'description' => 'Produsen Wine ikonik asal Australia yang terkenal dengan koleksi Red Wine dan White Wine berkualitas tinggi.',
            ],
            [
                'name' => 'Jack Daniel\'s',
                'slug' => 'jack-daniels',
                'logo' => "$baseUrl/uploads/brands/jack-daniels.png",
                'status' => 'ACTIVE',
                'order' => 8,
                'description' => 'Tennessee Whiskey legendaris dengan metode penyaringan charcoal mentega khas Lynchburg, Tennessee.',
            ],
            [
                'name' => 'Corona Extra',
                'slug' => 'corona-extra',
                'logo' => "$baseUrl/uploads/brands/corona-extra.png",
                'status' => 'ACTIVE',
                'order' => 9,
                'description' => 'Beer pilsner ringan asal Meksiko yang nikmat disajikan dingin dengan irisan buah lemon/jeruk nipis.',
            ],
        ];

        foreach ($brands as $brand) {
            Brand::updateOrCreate(
                ['slug' => $brand['slug']],
                $brand
            );
        }
    }
}