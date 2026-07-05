<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTblLabJobInvestorsTable extends Migration
{
    public function up()
    {
        Schema::create('tbl_lab_job_investors', function (Blueprint $table) {
            $table->unsignedBigInteger('lab_job_investor_id')->autoIncrement();
            $table->unsignedBigInteger('lab_job_id');
            $table->unsignedBigInteger('investor_id');
            $table->decimal('investment_basis', 15, 2)->default(0);
            $table->decimal('share_percentage', 8, 4)->default(0);
            $table->decimal('profit_share', 15, 2)->nullable();
            $table->timestamps();

            $table->foreign('lab_job_id')->references('lab_job_id')->on('tbl_lab_jobs')->cascadeOnDelete();
            $table->foreign('investor_id')->references('investor_id')->on('tbl_investors')->cascadeOnDelete();
            $table->unique(['lab_job_id', 'investor_id']);
        });

        $existingJobs = DB::table('tbl_lab_jobs')
            ->whereNotNull('investor_id')
            ->get(['lab_job_id', 'investor_id', 'profit_amount']);

        foreach ($existingJobs as $job) {
            DB::table('tbl_lab_job_investors')->insert([
                'lab_job_id' => $job->lab_job_id,
                'investor_id' => $job->investor_id,
                'investment_basis' => 0,
                'share_percentage' => 100,
                'profit_share' => $job->profit_amount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('tbl_lab_job_investors');
    }
}
