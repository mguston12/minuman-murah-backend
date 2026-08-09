<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoreSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('store_settings')->insert([
            'logo_website' => 'https://via.placeholder.com/200x50?text=Minuman+Murah',
            'favicon' => 'https://via.placeholder.com/32x32?text=K',
            'store_address' => 'Jakarta, Indonesia',
            'store_name' => 'Minuman Murah',
            'store_phone' => '+62-XXX-XXXX-XXXX',
            'store_email' => 'support@minumanmurah.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

