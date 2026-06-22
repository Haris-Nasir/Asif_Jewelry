<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultBankSeeder extends Seeder
{
    public function run()
    {
        $exists = DB::table('tbl_bank_details')->where('bank_details_status', 1)->exists();

        if ($exists) {
            return;
        }

        DB::table('tbl_bank_details')->insert([
            'bank_name' => 'Default Bank',
            'branch_name' => 'Main Branch',
            'account_no' => '0000000000000000',
            'ifsc_code' => 'SBIN0000000',
            'bank_details_status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
