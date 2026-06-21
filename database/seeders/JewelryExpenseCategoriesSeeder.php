<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JewelryExpenseCategoriesSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'Electricity Bill',
            'Rent',
            'Food',
            'Worker Salary',
            'Refinery Cost',
            'Other',
        ];

        foreach ($categories as $category) {
            $existing = DB::table('tbl_expense_categories')
                ->where('expense_category', $category)
                ->first();

            if ($existing) {
                DB::table('tbl_expense_categories')
                    ->where('expense_category_id', $existing->expense_category_id)
                    ->update([
                        'expense_category_status' => 1,
                        'updated_at' => now(),
                    ]);
                continue;
            }

            DB::table('tbl_expense_categories')->insert([
                'expense_category' => $category,
                'expense_category_status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
