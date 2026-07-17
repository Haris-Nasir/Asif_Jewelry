<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE tbl_brokers MODIFY broker_contact_no VARCHAR(11) NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE tbl_brokers MODIFY broker_contact_no VARCHAR(11) NOT NULL');
    }
};
