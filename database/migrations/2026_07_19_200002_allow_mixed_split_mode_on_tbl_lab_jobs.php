<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AllowMixedSplitModeOnTblLabJobs extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE tbl_lab_jobs MODIFY split_mode ENUM('investment', 'custom', 'mixed') NOT NULL DEFAULT 'custom'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE tbl_lab_jobs MODIFY split_mode ENUM('investment', 'custom') NOT NULL DEFAULT 'custom'");
    }
}
