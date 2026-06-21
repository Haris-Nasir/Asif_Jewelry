<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblInvestorsTable extends Migration
{
    public function up()
    {
        Schema::create('tbl_investors', function (Blueprint $table) {
            $table->unsignedBigInteger('investor_id')->autoIncrement();
            $table->unsignedBigInteger('user_id')->nullable()->unique();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('investor_name', 100);
            $table->string('contact_no', 15)->nullable();
            $table->string('email', 255)->nullable();
            $table->decimal('profit_share_percentage', 5, 2)->default(0);
            $table->boolean('investor_status')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tbl_investors');
    }
}
