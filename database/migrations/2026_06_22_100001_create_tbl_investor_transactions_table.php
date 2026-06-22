<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblInvestorTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('tbl_investor_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('investor_transaction_id')->autoIncrement();
            $table->unsignedBigInteger('investor_id');
            $table->foreign('investor_id')->references('investor_id')->on('tbl_investors')->cascadeOnDelete();
            $table->date('transaction_date');
            $table->enum('transaction_type', ['deposit', 'withdrawal', 'gold_buy', 'gold_sell']);
            $table->enum('metal_type', ['gold', 'silver'])->nullable();
            $table->decimal('weight_grams', 12, 3)->nullable();
            $table->decimal('rate_per_gram', 12, 2)->nullable();
            $table->decimal('amount', 15, 2);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->boolean('transaction_status')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tbl_investor_transactions');
    }
}
