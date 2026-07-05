<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropCashAmountFromTblLabJobsTable extends Migration
{
    public function up()
    {
        Schema::table('tbl_lab_jobs', function (Blueprint $table) {
            $table->dropColumn('cash_amount');
        });
    }

    public function down()
    {
        Schema::table('tbl_lab_jobs', function (Blueprint $table) {
            $table->decimal('cash_amount', 15, 2)->default(0)->after('base_price');
        });
    }
}
