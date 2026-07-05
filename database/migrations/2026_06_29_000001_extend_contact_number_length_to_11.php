<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ExtendContactNumberLengthTo11 extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE tbl_customers MODIFY customer_contact_no VARCHAR(11) NOT NULL');
        DB::statement('ALTER TABLE tbl_vendors MODIFY vendor_contact_no VARCHAR(11) NOT NULL');
        DB::statement('ALTER TABLE tbl_brokers MODIFY broker_contact_no VARCHAR(11) NOT NULL');
        DB::statement('ALTER TABLE tbl_investors MODIFY contact_no VARCHAR(11) NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE tbl_customers MODIFY customer_contact_no VARCHAR(10) NOT NULL');
        DB::statement('ALTER TABLE tbl_vendors MODIFY vendor_contact_no VARCHAR(10) NOT NULL');
        DB::statement('ALTER TABLE tbl_brokers MODIFY broker_contact_no VARCHAR(10) NOT NULL');
        DB::statement('ALTER TABLE tbl_investors MODIFY contact_no VARCHAR(15) NULL');
    }
}
