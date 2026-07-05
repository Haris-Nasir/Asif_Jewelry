<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblInvestorExpenseAllocationsTable extends Migration
{
    public function up()
    {
        Schema::create('tbl_investor_expense_allocations', function (Blueprint $table) {
            $table->unsignedBigInteger('investor_expense_allocation_id')->autoIncrement();
            $table->unsignedBigInteger('investor_id');
            $table->unsignedBigInteger('expense_id')->nullable();
            $table->date('allocation_date');
            $table->string('description', 255);
            $table->float('allocated_amount', 15)->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->boolean('allocation_status')->default(true);
            $table->timestamps();

            $table->foreign('investor_id')->references('investor_id')->on('tbl_investors');
            $table->foreign('expense_id')->references('expense_id')->on('tbl_expenses');
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tbl_investor_expense_allocations');
    }
}
