<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE tbl_customers MODIFY customer_contact_no VARCHAR(11) NULL');
        DB::statement('ALTER TABLE tbl_customers MODIFY customer_address VARCHAR(255) NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE tbl_customers MODIFY customer_contact_no VARCHAR(11) NOT NULL');
        DB::statement('ALTER TABLE tbl_customers MODIFY customer_address VARCHAR(255) NOT NULL');
    }
};
