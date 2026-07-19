<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSplitModeToTblLabJobs extends Migration
{
    public function up()
    {
        Schema::table('tbl_lab_jobs', function (Blueprint $table) {
            $table->enum('split_mode', ['investment', 'custom'])
                ->default('custom')
                ->after('notes');
        });
    }

    public function down()
    {
        Schema::table('tbl_lab_jobs', function (Blueprint $table) {
            $table->dropColumn('split_mode');
        });
    }
}
