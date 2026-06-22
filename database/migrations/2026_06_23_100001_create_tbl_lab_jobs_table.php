<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblLabJobsTable extends Migration
{
    public function up()
    {
        Schema::create('tbl_lab_jobs', function (Blueprint $table) {
            $table->unsignedBigInteger('lab_job_id')->autoIncrement();
            $table->date('job_date');
            $table->unsignedBigInteger('investor_id');
            $table->foreign('investor_id')->references('investor_id')->on('tbl_investors')->cascadeOnDelete();
            $table->string('job_reference', 50)->nullable();
            $table->enum('metal_type', ['gold', 'silver'])->default('gold');
            $table->decimal('weight_grams', 12, 3)->default(0);
            $table->decimal('base_price', 15, 2)->default(0);
            $table->decimal('cash_amount', 15, 2)->default(0);
            $table->decimal('refinery_cost', 15, 2)->default(0);
            $table->decimal('sold_amount', 15, 2)->nullable();
            $table->decimal('profit_amount', 15, 2)->nullable();
            $table->enum('job_status', ['open', 'sold'])->default('open');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->boolean('lab_job_status')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tbl_lab_jobs');
    }
}
