<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblInvoiceDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('tbl_invoice_details', function (Blueprint $table) {
            $table->unsignedBigInteger('invoice_detail_id')->autoIncrement();
            $table->unsignedBigInteger('invoice_mst_id');
            $table->unsignedBigInteger('sell_quality_id');
            $table->unsignedBigInteger('sell_category_id');
            $table->decimal('qty', 12, 3)->default(0);
            $table->string('qty_unit', 20)->default('pcs');
            $table->decimal('weight_grams', 12, 3)->default(0);
            $table->decimal('rate', 15, 2)->default(0);
            $table->decimal('base_amount', 15, 2)->default(0);
            $table->decimal('gst_percentage', 5, 2)->default(0);
            $table->decimal('gst_amount', 15, 2)->default(0);
            $table->decimal('sold_amount', 15, 2)->default(0);
            $table->decimal('cost_amount', 15, 2)->default(0);
            $table->decimal('profit_amount', 15, 2)->default(0);
            $table->boolean('invoice_detail_status')->default(true);
            $table->timestamps();

            $table->foreign('invoice_mst_id')
                ->references('invoice_mst_id')
                ->on('tbl_invoice_msts')
                ->cascadeOnDelete();
            $table->foreign('sell_quality_id')
                ->references('sell_quality_id')
                ->on('tbl_sell_qualities')
                ->restrictOnDelete();
            $table->foreign('sell_category_id')
                ->references('sell_quality_category_id')
                ->on('tbl_sell_quality_categories')
                ->restrictOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tbl_invoice_details');
    }
}
