<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddLabJobInvestorTransactions extends Migration
{
    public function up()
    {
        Schema::table('tbl_investor_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('lab_job_id')->nullable()->after('investor_id');
            $table->foreign('lab_job_id')->references('lab_job_id')->on('tbl_lab_jobs')->nullOnDelete();
        });

        DB::statement(
            "ALTER TABLE tbl_investor_transactions MODIFY transaction_type "
            . "ENUM('deposit', 'withdrawal', 'gold_buy', 'gold_sell', 'lab_purchase', 'lab_profit') NOT NULL"
        );
    }

    public function down()
    {
        Schema::table('tbl_investor_transactions', function (Blueprint $table) {
            $table->dropForeign(['lab_job_id']);
            $table->dropColumn('lab_job_id');
        });

        DB::statement(
            "ALTER TABLE tbl_investor_transactions MODIFY transaction_type "
            . "ENUM('deposit', 'withdrawal', 'gold_buy', 'gold_sell') NOT NULL"
        );
    }
}
