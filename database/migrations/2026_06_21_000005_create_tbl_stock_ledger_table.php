<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblStockLedgerTable extends Migration
{
    public function up()
    {
        Schema::create('tbl_stock_ledger', function (Blueprint $table) {
            $table->unsignedBigInteger('stock_ledger_id')->autoIncrement();
            $table->enum('metal_type', ['gold', 'silver']);
            $table->unsignedInteger('sell_quality_id')->nullable();
            $table->foreign('sell_quality_id')->references('sell_quality_id')->on('tbl_sell_qualities')->nullOnDelete();
            $table->enum('transaction_type', ['purchase', 'sale', 'adjustment', 'opening', 'laboratory']);
            $table->decimal('weight_grams', 12, 3)->default(0);
            $table->unsignedInteger('quantity_pieces')->default(0);
            $table->decimal('rate_per_gram', 15, 2)->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->decimal('balance_weight_after', 12, 3)->default(0);
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tbl_stock_ledger');
    }
}
