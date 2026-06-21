<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JewelryItemTypesSeeder extends Seeder
{
    public function run()
    {
        $goldCategoryId = $this->upsertCategory('Gold Items', 'gold');
        $silverCategoryId = $this->upsertCategory('Silver (Chandi) Items', 'silver');

        $goldItems = ['Tops', 'Angothi', 'Kanty', 'Balian', 'Kady Set'];
        $silverItems = ['Nail', 'Daddy', 'Pazeb', 'Tweeeez', 'Locket Set'];

        foreach ($goldItems as $item) {
            $this->upsertItem($goldCategoryId, $item);
        }

        foreach ($silverItems as $item) {
            $this->upsertItem($silverCategoryId, $item);
        }
    }

    private function upsertCategory(string $name, string $metalType): int
    {
        $existing = DB::table('tbl_sell_quality_categories')
            ->where('sell_category_name', $name)
            ->first();

        if ($existing) {
            DB::table('tbl_sell_quality_categories')
                ->where('sell_quality_category_id', $existing->sell_quality_category_id)
                ->update([
                    'metal_type' => $metalType,
                    'sell_quality_category_status' => 1,
                    'updated_at' => now(),
                ]);

            return $existing->sell_quality_category_id;
        }

        return DB::table('tbl_sell_quality_categories')->insertGetId([
            'sell_category_name' => $name,
            'metal_type' => $metalType,
            'sell_quality_category_status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function upsertItem(int $categoryId, string $name): void
    {
        $existing = DB::table('tbl_sell_qualities')
            ->where('sell_quality_category_id', $categoryId)
            ->where('quality_name', $name)
            ->first();

        if ($existing) {
            DB::table('tbl_sell_qualities')
                ->where('sell_quality_id', $existing->sell_quality_id)
                ->update([
                    'sell_quality_status' => 1,
                    'updated_at' => now(),
                ]);

            return;
        }

        DB::table('tbl_sell_qualities')->insert([
            'quality_name' => $name,
            'sell_quality_category_id' => $categoryId,
            'sell_quality_status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
