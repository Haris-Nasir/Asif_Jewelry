<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MakeInvoiceBankDetailsOptional extends Migration
{
    public function up()
    {
        $fk = collect(DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'tbl_invoice_msts'
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
              AND CONSTRAINT_NAME LIKE '%bank_details%'
        "))->pluck('CONSTRAINT_NAME');

        foreach ($fk as $name) {
            DB::statement("ALTER TABLE `tbl_invoice_msts` DROP FOREIGN KEY `{$name}`");
        }

        DB::statement('ALTER TABLE `tbl_invoice_msts` MODIFY `bank_details_id` INT UNSIGNED NULL');

        Schema::table('tbl_invoice_msts', function (Blueprint $table) {
            $table->foreign('bank_details_id')
                ->references('bank_details_id')
                ->on('tbl_bank_details')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        $fk = collect(DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'tbl_invoice_msts'
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
              AND CONSTRAINT_NAME LIKE '%bank_details%'
        "))->pluck('CONSTRAINT_NAME');

        foreach ($fk as $name) {
            DB::statement("ALTER TABLE `tbl_invoice_msts` DROP FOREIGN KEY `{$name}`");
        }

        DB::statement('UPDATE `tbl_invoice_msts` SET `bank_details_id` = 1 WHERE `bank_details_id` IS NULL');
        DB::statement('ALTER TABLE `tbl_invoice_msts` MODIFY `bank_details_id` INT UNSIGNED NOT NULL');

        Schema::table('tbl_invoice_msts', function (Blueprint $table) {
            $table->foreign('bank_details_id')
                ->references('bank_details_id')
                ->on('tbl_bank_details');
        });
    }
}
