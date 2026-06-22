<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultBrokerSeeder extends Seeder
{
    public function run()
    {
        $exists = DB::table('tbl_brokers')->where('broker_status', 1)->exists();

        if ($exists) {
            return;
        }

        DB::table('tbl_brokers')->insert([
            'broker_name' => 'Default Broker',
            'broker_contact_no' => '0000000000',
            'broker_status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
