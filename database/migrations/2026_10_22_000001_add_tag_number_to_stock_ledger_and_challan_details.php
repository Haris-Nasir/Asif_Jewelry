<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('tbl_stock_ledger', 'tag_number')) {
            Schema::table('tbl_stock_ledger', function (Blueprint $table) {
                $table->string('tag_number', 50)->nullable()->after('fine_weight');
                $table->index('tag_number');
            });
        }

        if (Schema::hasTable('tbl_challan_details') && !Schema::hasColumn('tbl_challan_details', 'tag_number')) {
            Schema::table('tbl_challan_details', function (Blueprint $table) {
                $table->string('tag_number', 50)->nullable();
                $table->index('tag_number');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('tbl_stock_ledger', 'tag_number')) {
            Schema::table('tbl_stock_ledger', function (Blueprint $table) {
                $table->dropIndex(['tag_number']);
                $table->dropColumn('tag_number');
            });
        }

        if (Schema::hasTable('tbl_challan_details') && Schema::hasColumn('tbl_challan_details', 'tag_number')) {
            Schema::table('tbl_challan_details', function (Blueprint $table) {
                $table->dropIndex(['tag_number']);
                $table->dropColumn('tag_number');
            });
        }
    }
};
