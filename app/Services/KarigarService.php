<?php

namespace App\Services;

use App\Models\tbl_karigar_job;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class KarigarService
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function issue(array $data, ?int $userId = null): tbl_karigar_job
    {
        return DB::transaction(function () use ($data, $userId) {
            $job = new tbl_karigar_job();
            $job->karigar_id = (int) $data['karigar_id'];
            $job->job_date = $data['job_date'];
            $job->metal_type = $data['metal_type'];
            $job->issued_weight_grams = (float) $data['issued_weight_grams'];
            $job->item_description = $data['item_description'] ?? null;
            $job->notes = $data['notes'] ?? null;
            $job->job_status = 'issued';
            $job->created_by = $userId;
            $job->save();

            $this->stockService->issueMetalToKarigar(
                $job->metal_type,
                (float) $job->issued_weight_grams,
                (int) $job->karigar_job_id,
                $userId,
                'Karigar outward #' . $job->karigar_job_id
            );

            return $job->fresh(['karigar', 'quality']);
        });
    }

    public function returnJob(tbl_karigar_job $job, array $data, ?int $userId = null): tbl_karigar_job
    {
        if ($job->job_status !== 'issued') {
            throw new RuntimeException('Only issued jobs can be returned.');
        }

        return DB::transaction(function () use ($job, $data, $userId) {
            $returnedWeight = (float) $data['returned_weight_grams'];
            $pieces = max(0, (int) ($data['returned_pieces'] ?? 0));
            $wastage = max(0, (float) ($data['wastage_grams'] ?? 0));
            $sellQualityId = (int) $data['sell_quality_id'];
            $mazduri = round(max((float) ($data['mazduri_cost'] ?? 0), 0), 2);

            if ($returnedWeight <= 0) {
                throw new RuntimeException('Returned weight must be greater than zero.');
            }

            $job->sell_quality_id = $sellQualityId;
            $job->returned_weight_grams = $returnedWeight;
            $job->returned_pieces = $pieces;
            $job->wastage_grams = $wastage;
            $job->mazduri_cost = $mazduri;
            $job->return_date = $data['return_date'] ?? now();
            $job->job_status = 'returned';
            $job->item_description = $data['item_description'] ?? $job->item_description;
            $job->notes = $data['notes'] ?? $job->notes;
            $job->save();

            $this->stockService->returnMetalFromKarigar(
                $job->metal_type,
                $sellQualityId,
                $returnedWeight,
                max(1, $pieces),
                (int) $job->karigar_job_id,
                $userId,
                'Karigar inward #' . $job->karigar_job_id
            );

            return $job->fresh(['karigar', 'quality']);
        });
    }

    public function linkJobToInvoice(int $karigarJobId, int $invoiceMstId): void
    {
        $job = tbl_karigar_job::where('karigar_job_id', $karigarJobId)
            ->where('karigar_job_status', true)
            ->where('job_status', 'returned')
            ->first();

        if (!$job) {
            throw new RuntimeException('Karigar job not found or not ready for sale.');
        }

        if ($job->invoice_mst_id && (int) $job->invoice_mst_id !== $invoiceMstId) {
            throw new RuntimeException('This karigar job is already linked to another invoice.');
        }

        $job->invoice_mst_id = $invoiceMstId;
        $job->save();
    }
}
