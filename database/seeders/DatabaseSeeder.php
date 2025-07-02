<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        DB::table('users')->insert([
            'name' => 'Super Admin',
            'username' => 'admin',
            'password' => Hash::make('password'),
        ]);


        DB::table('customers')->insert([
            [
                'name' => 'Andi Saputra',
                'address' => 'Jl. Kenanga No.5, Jakarta',
                'phone' => '081234567890',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Siti Aminah',
                'address' => 'Jl. Melati No.3, Bandung',
                'phone' => '081298765432',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Budi Santoso',
                'address' => 'Jl. Anggrek No.7, Surabaya',
                'phone' => '081345678901',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('categories')->insert([
            ['name' => 'Emas'],
            ['name' => 'Perak'],
            ['name' => 'Platina'],
        ]);

        // Types
        DB::table('types')->insert([
            ['name' => 'Cincin'],
            ['name' => 'Kalung'],
            ['name' => 'Gelang'],
        ]);

        // Karats
        DB::table('karats')->insert([
            ['karat' => '24K', 'rate' => 99.9],
            ['karat' => '22K', 'rate' => 91.6],
            ['karat' => '18K', 'rate' => 75.0],
        ]);


        DB::table('products')->insert([
            [
                'name' => 'Cincin Emas 24K',
                'category_id' => 1, // asumsi 'Emas' id = 1
                'type_id' => 1,     // asumsi 'Cincin' id = 1
                'karat_id' => 1,    // asumsi '24K' id = 1
                'weight' => 5.50,
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kalung Perak 22K',
                'category_id' => 2, // 'Perak'
                'type_id' => 2,     // 'Kalung'
                'karat_id' => 2,    // '22K'
                'weight' => 10.25,
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gelang Platina 18K',
                'category_id' => 3, // 'Platina'
                'type_id' => 3,     // 'Gelang'
                'karat_id' => 3,    // '18K'
                'weight' => 7.75,
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
