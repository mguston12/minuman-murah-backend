<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\CategoryBlog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class BlogSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $categories = [
            'rekomendasi' => CategoryBlog::where('slug', 'rekomendasi')->value('id'),
            'promo-diskon' => CategoryBlog::where('slug', 'promo-diskon')->value('id'),
            'tips-resep' => CategoryBlog::where('slug', 'tips-resep')->value('id'),
            'koleksi-terbaru' => CategoryBlog::where('slug', 'koleksi-terbaru')->value('id'),
        ];

        $blogs = [
            [
                'title' => '5 Rekomendasi Minuman Segar Favorit untuk Acara Pesta & Kumpul Keluarga',
                'slug' => '5-rekomendasi-minuman-segar-favorit-untuk-acara-pesta-kumpul-keluarga',
                'fk_category' => $categories['rekomendasi'] ?? null,
                'short_desc' => 'Bingung memilih minuman untuk acara spesial? Simak 5 rekomendasi minuman terbaik dengan harga terjangkau.',
                'long_desc' => '<p>Mengadakan acara kumpul keluarga atau pesta kurang lengkap tanpa sajian minuman yang menyegarkan. Di Minuman Murah, kami menyediakan berbagai pilihan produk berkualitas dengan harga terbaik. Berikut 5 rekomendasinya:</p>
<h2>1. Sparkling Juice Non-Alkohol</h2>
<p>Cocok untuk segala usia, memberikan kesan mewah dan meriah di meja hidangan Anda.</p>
<h2>2. Minuman Soda Rasa Buah</h2>
<p>Pilihan klasik yang selalu disukai anak-anak maupun dewasa saat bersantai bersama.</p>
<h2>3. Sirup Premium Rasa Eksotis</h2>
<p>Sangat pas dipadukan dengan es batu dan potongan buah segar untuk racikan es buah instant.</p>
<h2>4. Tehtarik & Milk Tea Kemasan</h2>
<p>Praktis dan siap minum untuk melengkapi hidangan penutup manis.</p>
<h2>5. Kopi Kekinian Siap Minum</h2>
<p>Pilihan favorit untuk menjaga semangat tamu dewasa sepanjang acara.</p>
<p>Dapatkan semua varian minuman di atas hanya di katalog Minuman Murah dengan penawaran grosir terbaik!</p>',
                'image_url' => 'https://images.pexels.com/photos/1283219/pexels-photo-1283219.jpeg',
                'meta_title' => '5 Rekomendasi Minuman Segar Favorit Acara Pesta',
                'meta_description' => 'Rekomendasi pilihan minuman terbaik dan terjangkau untuk melengkapi acara kumpul keluarga.',
                'hot_news' => true,
                'status' => true,
            ],
            [
                'title' => 'Promo Spesial Minuman Murah: Diskon Beli Grosir Bulan Ini',
                'slug' => 'promo-spesial-minuman-murah-diskon-beli-grosir-bulan-ini',
                'fk_category' => $categories['promo-diskon'] ?? null,
                'short_desc' => 'Nikmati promo potongan harga khusus pembelian kartonan dan grosir untuk stok toko Anda.',
                'long_desc' => '<p>Kabar gembira untuk para pemilik usaha warung, cafe, dan konsumen setia! Minuman Murah kembali menghadirkan promo diskon bulanan khusus untuk pembelian grosir.</p>
<h2>Keuntungan Promo Bulan Ini:</h2>
<p>1. Diskon hingga 15% untuk setiap pembelian minimal 5 karton varian tertentu.</p>
<p>2. Gratis ongkir wilayah perkotaan dengan minimal pemesanan.</p>
<p>3. Cashback saldo untuk transaksi melalui aplikasi atau website resmi kami.</p>
<p>Jangan sampai kehabisan! Stok promo terbatas hanya sampai akhir bulan ini.</p>',
                'image_url' => 'https://images.pexels.com/photos/5947019/pexels-photo-5947019.jpeg',
                'meta_title' => 'Promo Beli Grosir Minuman Murah Bulan Ini',
                'meta_description' => 'Dapatkan diskon potongan harga menarik untuk pemesanan grosir minuman favorit Anda.',
                'hot_news' => true,
                'status' => true,
            ],
            [
                'title' => 'Resep Mocktail Segar Simpel Menggunakan Bahan Minuman Murah',
                'slug' => 'resep-mocktail-segar-simpel-menggunakan-bahan-minuman-murah',
                'fk_category' => $categories['tips-resep'] ?? null,
                'short_desc' => 'Buat sendiri minuman berkelas ala cafe di rumah dengan racikan hemat dan mudah.',
                'long_desc' => '<p>Ingin menikmati minuman ala cafe tanpa perlu merogoh kocek dalam? Anda bisa meracik mocktail segar sendiri di rumah dengan bahan-bahan yang bisa dibeli langsung di Minuman Murah!</p>
<h2>Bahan-bahan:</h2>
<p>- 100ml Sirup Melon/Jeruk</p>
<p>- 150ml Minuman Soda Bening (Sprite/Seven Up)</p>
<p>- Es batu secukupnya</p>
<p>- Biji selasih & irisan lemon untuk hiasan</p>
<h2>Cara Membuat:</h2>
<p>1. Tuang sirup ke dalam gelas yang sudah diisi es batu.</p>
<p>2. Tuangkan minuman soda perlahan untuk membuat efek lapisan warna.</p>
<p>3. Tambahkan biji selasih dan irisan lemon di atasnya.</p>
<p>4. Mocktail siap disajikan!</p>',
                'image_url' => 'https://images.pexels.com/photos/1187766/pexels-photo-1187766.jpeg',
                'meta_title' => 'Resep Mocktail Simpel Hemat ala Cafe',
                'meta_description' => 'Cara praktis membuat racikan mocktail segar hemat biaya menggunakan bahan-bahan siap pakai.',
                'hot_news' => false,
                'status' => true,
            ],
            [
                'title' => 'Panduan Menyimpan Minuman Kemasan Agar Tetap Segar & Tahan Lama',
                'slug' => 'panduan-menyimpan-minuman-kemasan-agar-tetap-segar-tahan-lama',
                'fk_category' => $categories['tips-resep'] ?? null,
                'short_desc' => 'Tips penting bagi pemilik usaha dan konsumen dalam menyimpan stok minuman kemasan.',
                'long_desc' => '<p>Penyimpanan yang salah dapat menurunkan kualitas dan rasa dari minuman kemasan. Agar stok minuman di rumah atau toko Anda tetap awet dan segar, ikuti tips berikut:</p>
<h2>1. Hindari Sinar Matahari Langsung</h2>
<p>Sinar matahari dan suhu panas dapat mengubah rasa serta merusak kemasan plastik maupun kaleng.</p>
<h2>2. Simpan di Tempat Sejuk & Kering</h2>
<p>Pastikan gudang atau area penyimpanan memiliki sirkulasi udara yang baik dan bebas dari kelembapan berlebih.</p>
<h2>3. Terapkan Sistem FIFO (First In, First Out)</h2>
<p>Gunakan atau jual produk yang memiliki tanggal kedaluwarsa lebih awal terlebih dahulu.</p>
<h2>4. Perhatikan Suhu Pendingin</h2>
<p>Untuk minuman yang membutuhkan suhu dingin, pastikan kulkas stabil di suhu 2°C - 5°C.</p>',
                'image_url' => 'https://images.pexels.com/photos/2983101/pexels-photo-2983101.jpeg',
                'meta_title' => 'Cara Menyimpan Minuman Kemasan yang Benar',
                'meta_description' => 'Tips menjaga kesegaran dan daya simpan minuman kemasan botol mau pun kaleng.',
                'hot_news' => false,
                'status' => true,
            ],
            [
                'title' => 'Produk Baru: Koleksi Minuman Herbal & Kesehatan Siap Minum',
                'slug' => 'produk-baru-koleksi-minuman-herbal-kesehatan-siap-minum',
                'fk_category' => $categories['koleksi-terbaru'] ?? null,
                'short_desc' => 'Minuman Murah kini menghadirkan varian minuman herbal dan alami untuk menjaga daya tahan tubuh.',
                'long_desc' => '<p>Gaya hidup sehat kini semakin mudah! Minuman Murah resmi meluncurkan kategori produk baru yaitu Minuman Herbal & Kesehatan Siap Minum.</p>
<h2>Pilihan Varian Terbaru:</h2>
<p>1. <strong>Liang Teh Alami:</strong> Menyegarkan dan membantu meredakan panas dalam.</p>
<p>2. <strong>Sari Kunyit Asam & Jahe Merah:</strong> Minuman rempah tradisional kemasan modern yang practical dikonsumsi.</p>
<p>3. <strong>Air Kelapa Murni 100%:</strong> Kaya akan elektrolit alami untuk mengembalikan hidrasi tubuh.</p>
<p>Dapatkan koleksi terbaru ini sekarang dengan harga promo perkenalan khusus minggu ini!</p>',
                'image_url' => 'https://images.pexels.com/photos/1638280/pexels-photo-1638280.jpeg',
                'meta_title' => 'Koleksi Minuman Herbal Kesehatan Terbaru',
                'meta_description' => 'Temukan varian minuman kesehatan dan herbal alami siap minum hanya di Minuman Murah.',
                'hot_news' => true,
                'status' => true,
            ],
        ];

        $disk = Storage::disk('public');

        foreach ($blogs as $blog) {
            $filename = $blog['slug'] . '.jpg';
            $coverPath = 'blogs/' . $filename;

            if (!$disk->exists($coverPath)) {
                $ctx = stream_context_create(['http' => ['timeout' => 10, 'user_agent' => 'Mozilla/5.0']]);
                $imageData = @file_get_contents($blog['image_url'] . '?auto=compress&cs=tinysrgb&w=800&h=500&fit=crop', false, $ctx);

                if ($imageData === false && function_exists('imagecreatetruecolor')) {
                    $img = imagecreatetruecolor(800, 500);
                    $bg = imagecolorallocate($img, mt_rand(20, 60), mt_rand(40, 80), mt_rand(100, 160));
                    imagefill($img, 0, 0, $bg);
                    $white = imagecolorallocate($img, 255, 255, 255);
                    $text = $blog['title'];
                    $fontSize = 5;
                    $tw = imagefontwidth($fontSize) * strlen($text);
                    $th = imagefontheight($fontSize);
                    $x = (imagesx($img) - $tw) / 2;
                    $y = (imagesy($img) - $th) / 2;
                    imagestring($img, $fontSize, (int)$x, (int)$y, $text, $white);
                    ob_start();
                    imagejpeg($img, null, 80);
                    $imageData = ob_get_clean();
                    imagedestroy($img);
                }

                if ($imageData) {
                    $disk->put($coverPath, $imageData);
                }
            }

            Blog::updateOrCreate(
                ['slug' => $blog['slug']],
                [
                    'cover' => $disk->exists($coverPath) ? $coverPath : null,
                    'title' => $blog['title'],
                    'short_desc' => $blog['short_desc'],
                    'long_desc' => $blog['long_desc'],
                    'fk_category' => $blog['fk_category'],
                    'slug' => $blog['slug'],
                    'meta_title' => $blog['meta_title'],
                    'meta_description' => $blog['meta_description'],
                    'hot_news' => $blog['hot_news'],
                    'status' => $blog['status'],
                ]
            );
        }
    }
}