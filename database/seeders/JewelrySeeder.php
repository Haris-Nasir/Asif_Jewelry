<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\tbl_metal_balance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class JewelrySeeder extends Seeder
{
    public function run()
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@ayyubjewelers.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        $worker = User::updateOrCreate(
            ['email' => 'worker@ayyubjewelers.com'],
            [
                'name' => 'Worker',
                'password' => Hash::make('password'),
                'role' => 'worker',
                'permissions' => config('permissions.worker_defaults', []),
            ]
        );

        tbl_metal_balance::updateOrCreate(
            ['metal_type' => 'gold'],
            ['total_weight_grams' => 0, 'total_pieces' => 0]
        );

        tbl_metal_balance::updateOrCreate(
            ['metal_type' => 'silver'],
            ['total_weight_grams' => 0, 'total_pieces' => 0]
        );

        $this->call([
            JewelryItemTypesSeeder::class,
            JewelryExpenseCategoriesSeeder::class,
        ]);
    }
}
