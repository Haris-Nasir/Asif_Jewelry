<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSaleProcessingCostsToInvoices extends Migration
{
    public function up()
    {
        Schema::table('tbl_invoice_msts', function (Blueprint $table) {
            $table->decimal('refinery_cost', 15, 2)->default(0)->after('profit_amount');
            $table->decimal('polish_rate_per_gram', 12, 2)->default(0)->after('refinery_cost');
            $table->decimal('polish_cost', 15, 2)->default(0)->after('polish_rate_per_gram');
            $table->decimal('mazduri_cost', 15, 2)->default(0)->after('polish_cost');
        });
    }

    public function down()
    {
        Schema::table('tbl_invoice_msts', function (Blueprint $table) {
            $table->dropColumn(['refinery_cost', 'polish_rate_per_gram', 'polish_cost', 'mazduri_cost']);
        });
    }
}
