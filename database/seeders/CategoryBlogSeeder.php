<?php

namespace Database\Seeders;

use App\Models\CategoryBlog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoryBlogSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
    [
        'name' => 'Rekomendasi',
        'slug' => 'rekomendasi',
        'description' => 'Rekomendasi pilihan minuman terbaik dengan harga terjangkau.',
        'status' => true,
    ],
    [
        'name' => 'Promo & Diskon',
        'slug' => 'promo-diskon',
        'description' => 'Informasi diskon, promo grosir, edisi terbatas, dan penawaran harga terbaik.',
        'status' => true,
    ],
    [
        'name' => 'Tips & Resep',
        'slug' => 'tips-resep',
        'description' => 'Panduan penyimpanan, resep racikan mocktail, hingga tips menikmati minuman.',
        'status' => true,
    ],
    [
        'name' => 'Koleksi Terbaru',
        'slug' => 'koleksi-terbaru',
        'description' => 'Ulasan produk baru, varian terfavorit, dan update persediaan stok.',
        'status' => true,
    ],
    ];

        foreach ($categories as $category) {
            CategoryBlog::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}