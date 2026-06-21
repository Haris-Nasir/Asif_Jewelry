<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetalTypeToSellQualityCategories extends Migration
{
    public function up()
    {
        Schema::table('tbl_sell_quality_categories', function (Blueprint $table) {
            $table->enum('metal_type', ['gold', 'silver'])->default('gold')->after('sell_category_name');
        });
    }

    public function down()
    {
        Schema::table('tbl_sell_quality_categories', function (Blueprint $table) {
            $table->dropColumn('metal_type');
        });
    }
}
