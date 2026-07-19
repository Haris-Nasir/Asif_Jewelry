<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfitSplitModeToTblInvestors extends Migration
{
    public function up()
    {
        Schema::table('tbl_investors', function (Blueprint $table) {
            $table->enum('profit_split_mode', ['investment', 'custom'])
                ->default('investment')
                ->after('profit_share_percentage');
        });
    }

    public function down()
    {
        Schema::table('tbl_investors', function (Blueprint $table) {
            $table->dropColumn('profit_split_mode');
        });
    }
}
