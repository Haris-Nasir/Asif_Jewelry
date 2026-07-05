<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tbl_invoice_msts', function (Blueprint $table) {
            $table->unsignedBigInteger('karigar_job_id')->nullable()->after('mazduri_cost');
        });
    }

    public function down()
    {
        Schema::table('tbl_invoice_msts', function (Blueprint $table) {
            $table->dropColumn('karigar_job_id');
        });
    }
};
