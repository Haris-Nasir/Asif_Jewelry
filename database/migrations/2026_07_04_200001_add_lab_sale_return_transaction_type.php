<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddLabSaleReturnTransactionType extends Migration
{
    public function up()
    {
        DB::statement(
            "ALTER TABLE tbl_investor_transactions MODIFY transaction_type "
            . "ENUM('deposit', 'withdrawal', 'gold_buy', 'gold_sell', 'lab_purchase', 'lab_profit', 'lab_sale_return') NOT NULL"
        );

        $soldJobs = DB::table('tbl_lab_jobs')
            ->where('lab_job_status', true)
            ->where('job_status', 'sold')
            ->whereNotNull('sold_amount')
            ->get(['lab_job_id', 'job_date', 'metal_type', 'job_reference', 'created_by']);

        foreach ($soldJobs as $job) {
            $participants = DB::table('tbl_lab_job_investors')
                ->where('lab_job_id', $job->lab_job_id)
                ->get(['investor_id', 'investment_basis']);

            foreach ($participants as $participant) {
                $exists = DB::table('tbl_investor_transactions')
                    ->where('lab_job_id', $job->lab_job_id)
                    ->where('investor_id', $participant->investor_id)
                    ->where('transaction_type', 'lab_sale_return')
                    ->where('transaction_status', true)
                    ->exists();

                if ($exists || (float) $participant->investment_basis <= 0) {
                    continue;
                }

                $reference = $job->job_reference ?: ('#' . $job->lab_job_id);

                DB::table('tbl_investor_transactions')->insert([
                    'investor_id' => $participant->investor_id,
                    'lab_job_id' => $job->lab_job_id,
                    'transaction_date' => $job->job_date,
                    'transaction_type' => 'lab_sale_return',
                    'metal_type' => $job->metal_type,
                    'amount' => $participant->investment_basis,
                    'notes' => 'Lab job capital returned (' . $reference . ')',
                    'created_by' => $job->created_by,
                    'transaction_status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down()
    {
        DB::table('tbl_investor_transactions')
            ->where('transaction_type', 'lab_sale_return')
            ->delete();

        DB::statement(
            "ALTER TABLE tbl_investor_transactions MODIFY transaction_type "
            . "ENUM('deposit', 'withdrawal', 'gold_buy', 'gold_sell', 'lab_purchase', 'lab_profit') NOT NULL"
        );
    }
}
