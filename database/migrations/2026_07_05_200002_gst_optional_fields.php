<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE tbl_customers MODIFY customer_gst_no VARCHAR(15) NULL');
        DB::statement('ALTER TABLE tbl_customers MODIFY customer_gst_code VARCHAR(2) NULL');
        DB::statement('ALTER TABLE tbl_vendors MODIFY vendor_gst_no VARCHAR(15) NULL');
        DB::statement('ALTER TABLE tbl_vendors MODIFY vendor_gst_code VARCHAR(2) NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE tbl_customers MODIFY customer_gst_no VARCHAR(15) NOT NULL');
        DB::statement('ALTER TABLE tbl_customers MODIFY customer_gst_code VARCHAR(2) NOT NULL');
        DB::statement('ALTER TABLE tbl_vendors MODIFY vendor_gst_no VARCHAR(15) NOT NULL');
        DB::statement('ALTER TABLE tbl_vendors MODIFY vendor_gst_code VARCHAR(2) NOT NULL');
    }
};
