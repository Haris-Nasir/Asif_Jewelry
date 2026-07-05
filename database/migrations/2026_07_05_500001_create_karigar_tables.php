<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tbl_karigars', function (Blueprint $table) {
            $table->unsignedBigInteger('karigar_id')->autoIncrement();
            $table->string('karigar_name', 100);
            $table->string('contact_no', 11)->nullable();
            $table->string('address', 255)->nullable();
            $table->boolean('karigar_status')->default(true);
            $table->timestamps();
        });

        Schema::create('tbl_karigar_jobs', function (Blueprint $table) {
            $table->unsignedBigInteger('karigar_job_id')->autoIncrement();
            $table->unsignedBigInteger('karigar_id');
            $table->foreign('karigar_id')->references('karigar_id')->on('tbl_karigars');
            $table->dateTime('job_date');
            $table->enum('metal_type', ['gold', 'silver']);
            $table->unsignedInteger('sell_quality_id')->nullable();
            $table->foreign('sell_quality_id')->references('sell_quality_id')->on('tbl_sell_qualities')->nullOnDelete();
            $table->decimal('issued_weight_grams', 12, 3);
            $table->decimal('returned_weight_grams', 12, 3)->nullable();
            $table->decimal('wastage_grams', 12, 3)->default(0);
            $table->unsignedInteger('returned_pieces')->default(0);
            $table->string('item_description', 255)->nullable();
            $table->decimal('mazduri_cost', 15, 2)->default(0);
            $table->enum('job_status', ['issued', 'returned', 'cancelled'])->default('issued');
            $table->dateTime('return_date')->nullable();
            $table->unsignedBigInteger('invoice_mst_id')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('karigar_job_status')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tbl_karigar_jobs');
        Schema::dropIfExists('tbl_karigars');
    }
};
