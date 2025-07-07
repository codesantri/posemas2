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
        ]);

        // Types
        DB::table('types')->insert([
            ['name' => 'Cincin'],
            ['name' => 'Kalung'],
            ['name' => 'Gelang'],
        ]);

        // Karats
        DB::table('karats')->insert([
            ['name' => '24K',],
            ['name' => '99.9 %'],
            ['name' => '91.6 %'],
            ['name' => '75.0 %'],
            ['name' => '22K',],
            ['name' => '18K',],
        ]);


        DB::table('products')->insert([
            [
                'name' => 'Cincin Emas 24K',
                'category_id' => 1, // Emas
                'type_id' => 1,     // Cincin
                'karat_id' => 1,    // 24K
                'weight' => 5.50,
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kalung Perak 22K',
                'category_id' => 1, // Perak
                'type_id' => 2,     // Kalung
                'karat_id' => 5,    // 22K
                'weight' => 10.25,
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gelang Platina 18K',
                'category_id' => 1, // Platina
                'type_id' => 3,     // Gelang
                'karat_id' => 6,    // 18K
                'weight' => 7.75,
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kalung Emas 75%',
                'category_id' => 1,
                'type_id' => 2,
                'karat_id' => 4,    // 75.0%
                'weight' => 8.30,
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cincin Emas 91.6%',
                'category_id' => 1,
                'type_id' => 1,
                'karat_id' => 3,    // 91.6%
                'weight' => 4.10,
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gelang Emas 99.9%',
                'category_id' => 1,
                'type_id' => 3,
                'karat_id' => 2,    // 99.9%
                'weight' => 6.60,
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kalung Emas 18K',
                'category_id' => 1,
                'type_id' => 2,
                'karat_id' => 6,    // 18K
                'weight' => 9.45,
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cincin Emas 22K',
                'category_id' => 1,
                'type_id' => 1,
                'karat_id' => 5,    // 22K
                'weight' => 3.85,
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
