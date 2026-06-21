<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblMetalBalancesTable extends Migration
{
    public function up()
    {
        Schema::create('tbl_metal_balances', function (Blueprint $table) {
            $table->enum('metal_type', ['gold', 'silver'])->primary();
            $table->decimal('total_weight_grams', 12, 3)->default(0);
            $table->unsignedInteger('total_pieces')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tbl_metal_balances');
    }
}
