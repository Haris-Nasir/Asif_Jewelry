<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTblAppSettingsAndLabShopProfit extends Migration
{
    public function up()
    {
        Schema::create('tbl_app_settings', function (Blueprint $table) {
            $table->string('setting_key', 100)->primary();
            $table->text('setting_value')->nullable();
            $table->timestamps();
        });

        DB::table('tbl_app_settings')->insert([
            [
                'setting_key' => 'shop_profit_percentage',
                'setting_value' => '0',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'investor_split_mode',
                'setting_value' => 'custom',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Schema::table('tbl_lab_jobs', function (Blueprint $table) {
            $table->decimal('shop_profit_percentage', 5, 2)->default(0)->after('split_mode');
            $table->decimal('shop_profit_amount', 15, 2)->nullable()->after('shop_profit_percentage');
        });
    }

    public function down()
    {
        Schema::table('tbl_lab_jobs', function (Blueprint $table) {
            $table->dropColumn(['shop_profit_percentage', 'shop_profit_amount']);
        });

        Schema::dropIfExists('tbl_app_settings');
    }
}
