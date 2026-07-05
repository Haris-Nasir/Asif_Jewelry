<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE tbl_challan_msts MODIFY challan_date DATETIME NOT NULL');
        DB::statement('ALTER TABLE tbl_inward_msts MODIFY inward_mst_date DATETIME NOT NULL');
        DB::statement('ALTER TABLE tbl_invoice_msts MODIFY invoice_date DATETIME NOT NULL');
        DB::statement('ALTER TABLE tbl_lab_jobs MODIFY job_date DATETIME NOT NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE tbl_challan_msts MODIFY challan_date DATE NOT NULL');
        DB::statement('ALTER TABLE tbl_inward_msts MODIFY inward_mst_date DATE NOT NULL');
        DB::statement('ALTER TABLE tbl_invoice_msts MODIFY invoice_date DATE NOT NULL');
        DB::statement('ALTER TABLE tbl_lab_jobs MODIFY job_date DATE NOT NULL');
    }
};
