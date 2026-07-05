<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangeInvestorTransactionDateToDatetime extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE tbl_investor_transactions MODIFY transaction_date DATETIME NOT NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE tbl_investor_transactions MODIFY transaction_date DATE NOT NULL');
    }
}
