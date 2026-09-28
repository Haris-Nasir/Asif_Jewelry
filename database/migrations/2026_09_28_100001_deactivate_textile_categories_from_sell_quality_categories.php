<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DeactivateTextileCategoriesFromSellQualityCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tbl_sell_quality_categories')) {
            DB::table('tbl_sell_quality_categories')
                ->whereIn(DB::raw('LOWER(sell_category_name)'), ['roll', 'beam', 'grey'])
                ->update(['sell_quality_category_status' => 0]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('tbl_sell_quality_categories')) {
            DB::table('tbl_sell_quality_categories')
                ->whereIn(DB::raw('LOWER(sell_category_name)'), ['roll', 'beam', 'grey'])
                ->update(['sell_quality_category_status' => 1]);
        }
    }
}
