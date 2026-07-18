<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\tbl_investor;
use App\Models\tbl_metal_balance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class JewelrySeeder extends Seeder
{
    public function run()
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@ayubjewelers.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        $worker = User::updateOrCreate(
            ['email' => 'worker@ayubjewelers.com'],
            [
                'name' => 'Worker',
                'password' => Hash::make('password'),
                'role' => 'worker',
                'permissions' => config('permissions.worker_defaults', []),
            ]
        );

        $investorUser = User::updateOrCreate(
            ['email' => 'investor@ayubjewelers.com'],
            [
                'name' => 'Investor',
                'password' => Hash::make('password'),
                'role' => 'investor',
            ]
        );

        tbl_investor::updateOrCreate(
            ['user_id' => $investorUser->id],
            [
                'investor_name' => 'Default Investor',
                'contact_no' => '9999999999',
                'email' => 'investor@ayubjewelers.com',
                'profit_share_percentage' => 25.00,
                'investor_status' => true,
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
