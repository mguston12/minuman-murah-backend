<?php

namespace Database\Seeders;

use App\Models\GeneralSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GeneralSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        GeneralSetting::create([
            'phone' => '+62-812-3456-7890',
            'email' => 'info@minumanmurah.com',
            'instagram' => 'https://instagram.com/minumanmurah',
            'tiktok' => 'https://tiktok.com/@minumanmurah',
            'facebook' => 'https://facebook.com/minumanmurah',
            'youtube' => 'https://youtube.com/@minumanmurah',
            'pinterest' => 'https://pinterest.com/minumanmurah',
            'location' => 'Jl. Merdeka No. 123, Jakarta, Indonesia',
        ]);

        $this->command->info('General Settings seeded successfully!');
    }
}

