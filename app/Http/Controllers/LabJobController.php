<?php

namespace App\Http\Controllers;

use App\Models\tbl_investor;
use App\Models\tbl_lab_job;
use App\Models\tbl_lab_job_investor;
use App\Services\LabJobService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Concerns\AuditsActions;

class LabJobController extends Controller
{
    use AuditsActions;

    protected LabJobService $labJobService;

    public function __construct(LabJobService $labJobService)
    {
        $this->labJobService = $labJobService;
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'investor_id' => 'nullable|integer|exists:tbl_investors,investor_id',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'job_status' => 'nullable|in:open,sold',
            'paginate' => 'nullable|integer|min:1|max:100',
        ]);

        $paginate = $validated['paginate'] ?? 15;
        $query = tbl_lab_job::with([
            'investor:investor_id,investor_name',
            'jobInvestors.investor:investor_id,investor_name',
        ])
            ->where('lab_job_status', true)
            ->orderByDesc('job_date')
            ->orderByDesc('lab_job_id');

        $investor = $this->resolveInvestorScope($request, $validated['investor_id'] ?? null);
        if ($investor) {
            $query->whereHas('jobInvestors', function ($q) use ($investor) {
                $q->where('investor_id', $investor->investor_id);
            });
        }

        if (!empty($validated['from_date']) && !empty($validated['to_date'])) {
            $query->whereDate('job_date', '>=', $validated['from_date'])
                ->whereDate('job_date', '<=', $validated['to_date']);
        }

        if (!empty($validated['job_status'])) {
            $query->where('job_status', $validated['job_status']);
        }

        $paginator = $query->paginate($paginate);
        $scopedInvestorId = $investor ? $investor->investor_id : null;
        $paginator->getCollection()->transform(function ($job) use ($scopedInvestorId) {
            return $this->formatJobResponse($job, $scopedInvestorId);
        });

        return response()->json($paginator);
    }

    public function previewShares(Request $request)
    {
        $validated = $request->validate([
            'investor_ids' => 'required|array|min:1',
            'investor_ids.*' => 'integer|exists:tbl_investors,investor_id',
            'base_price' => 'nullable|numeric|min:0',
            'profit_amount' => 'nullable|numeric',
        ]);

        try {
            return response()->json(
                $this->labJobService->previewInvestorShares(
                    $validated['investor_ids'],
                    isset($validated['base_price']) ? (float) $validated['base_price'] : null,
                    isset($validated['profit_amount']) ? (float) $validated['profit_amount'] : null
                )
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status' => -1,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_date' => 'required|date',
            'investor_ids' => 'required|array|min:1',
            'investor_ids.*' => 'integer|exists:tbl_investors,investor_id',
            'job_reference' => 'nullable|string|max:50',
            'metal_type' => ['required', Rule::in(['gold', 'silver'])],
            'weight_grams' => 'required|numeric|min:0',
            'base_price' => 'required|numeric|min:0',
            'refinery_cost' => 'nullable|numeric|min:0',
            'sold_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $soldAmount = isset($validated['sold_amount']) ? (float) $validated['sold_amount'] : null;
        $profit = $this->calculateProfit(
            $soldAmount,
            (float) $validated['base_price'],
            (float) ($validated['refinery_cost'] ?? 0)
        );

        try {
            DB::beginTransaction();

            $job = tbl_lab_job::create([
                'job_date' => $validated['job_date'],
                'investor_id' => $validated['investor_ids'][0],
                'job_reference' => $validated['job_reference'] ?? null,
                'metal_type' => $validated['metal_type'],
                'weight_grams' => $validated['weight_grams'],
                'base_price' => $validated['base_price'],
                'refinery_cost' => $validated['refinery_cost'] ?? 0,
                'sold_amount' => $soldAmount,
                'profit_amount' => $profit,
                'job_status' => $soldAmount !== null ? 'sold' : 'open',
                'notes' => $validated['notes'] ?? null,
                'created_by' => optional($request->user())->id,
                'lab_job_status' => true,
            ]);

            $this->labJobService->finalizeJobFinancing(
                $job,
                $validated['investor_ids'],
                (float) $validated['base_price'],
                $profit,
                optional($request->user())->id
            );

            DB::commit();
        } catch (\InvalidArgumentException $e) {
            DB::rollBack();
            return response()->json([
                'status' => -1,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $this->audit('create', 'laboratory', $job->lab_job_id, 'Lab job created with ' . count($validated['investor_ids']) . ' investor(s)');

        return response()->json([
            'status' => 1,
            'message' => 'Laboratory job created successfully.',
            'data' => $this->formatJobResponse($job->fresh()->load([
                'investor:investor_id,investor_name',
                'jobInvestors.investor:investor_id,investor_name',
            ])),
        ]);
    }

    public function update(Request $request, int $labJobId)
    {
        $job = tbl_lab_job::where('lab_job_id', $labJobId)
            ->where('lab_job_status', true)
            ->firstOrFail();

        $validated = $request->validate([
            'job_date' => 'required|date',
            'investor_ids' => 'required|array|min:1',
            'investor_ids.*' => 'integer|exists:tbl_investors,investor_id',
            'job_reference' => 'nullable|string|max:50',
            'metal_type' => ['required', Rule::in(['gold', 'silver'])],
            'weight_grams' => 'required|numeric|min:0',
            'base_price' => 'required|numeric|min:0',
            'refinery_cost' => 'nullable|numeric|min:0',
            'sold_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $soldAmount = isset($validated['sold_amount']) ? (float) $validated['sold_amount'] : null;
        $profit = $this->calculateProfit(
            $soldAmount,
            (float) $validated['base_price'],
            (float) ($validated['refinery_cost'] ?? 0)
        );

        try {
            DB::beginTransaction();

            $job->update([
                'job_date' => $validated['job_date'],
                'investor_id' => $validated['investor_ids'][0],
                'job_reference' => $validated['job_reference'] ?? null,
                'metal_type' => $validated['metal_type'],
                'weight_grams' => $validated['weight_grams'],
                'base_price' => $validated['base_price'],
                'refinery_cost' => $validated['refinery_cost'] ?? 0,
                'sold_amount' => $soldAmount,
                'profit_amount' => $profit,
                'job_status' => $soldAmount !== null ? 'sold' : 'open',
                'notes' => $validated['notes'] ?? null,
            ]);

            $this->labJobService->finalizeJobFinancing(
                $job,
                $validated['investor_ids'],
                (float) $validated['base_price'],
                $profit,
                optional($request->user())->id
            );

            DB::commit();
        } catch (\InvalidArgumentException $e) {
            DB::rollBack();
            return response()->json([
                'status' => -1,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            'status' => 1,
            'message' => 'Laboratory job updated successfully.',
            'data' => $this->formatJobResponse($job->fresh()->load([
                'investor:investor_id,investor_name',
                'jobInvestors.investor:investor_id,investor_name',
            ])),
        ]);
    }

    public function destroy(int $labJobId)
    {
        $job = tbl_lab_job::where('lab_job_id', $labJobId)
            ->where('lab_job_status', true)
            ->firstOrFail();

        $job->lab_job_status = false;
        $job->save();

        $this->labJobService->reverseJobTransactions($labJobId);

        return response()->json([
            'status' => 1,
            'message' => 'Laboratory job deleted successfully.',
        ]);
    }

    public function summary(Request $request)
    {
        $validated = $request->validate([
            'investor_id' => 'nullable|integer|exists:tbl_investors,investor_id',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
        ]);

        $investor = $this->resolveInvestorScope($request, $validated['investor_id'] ?? null);
        $query = tbl_lab_job::where('lab_job_status', true);

        if ($investor) {
            $query->whereHas('jobInvestors', function ($q) use ($investor) {
                $q->where('investor_id', $investor->investor_id);
            });
        }

        if (!empty($validated['from_date']) && !empty($validated['to_date'])) {
            $query->whereDate('job_date', '>=', $validated['from_date'])
                ->whereDate('job_date', '<=', $validated['to_date']);
        }

        $jobs = (clone $query)->get();
        $soldJobs = $jobs->where('job_status', 'sold');
        $totalProfit = (float) $soldJobs->sum('profit_amount');

        if ($investor) {
            $totalProfit = (float) tbl_lab_job_investor::query()
                ->where('investor_id', $investor->investor_id)
                ->whereHas('labJob', function ($q) use ($validated) {
                    $q->where('lab_job_status', true)
                        ->where('job_status', 'sold');

                    if (!empty($validated['from_date']) && !empty($validated['to_date'])) {
                        $q->whereDate('job_date', '>=', $validated['from_date'])
                            ->whereDate('job_date', '<=', $validated['to_date']);
                    }
                })
                ->sum('profit_share');
        }

        return response()->json([
            'total_jobs' => $jobs->count(),
            'open_jobs' => $jobs->where('job_status', 'open')->count(),
            'sold_jobs' => $soldJobs->count(),
            'total_weight_grams' => round($jobs->sum('weight_grams'), 3),
            'total_lab_profit' => round($totalProfit, 2),
        ]);
    }

    private function formatJobResponse(tbl_lab_job $job, ?int $scopedInvestorId = null): array
    {
        $participants = $job->jobInvestors->map(function ($row) {
            return [
                'investor_id' => $row->investor_id,
                'investor_name' => optional($row->investor)->investor_name,
                'investment_basis' => (float) $row->investment_basis,
                'share_percentage' => (float) $row->share_percentage,
                'profit_share' => $row->profit_share !== null ? (float) $row->profit_share : null,
            ];
        })->values()->all();

        $payload = $job->toArray();
        $payload['participants'] = $participants;

        if ($scopedInvestorId) {
            $participant = collect($participants)->firstWhere('investor_id', $scopedInvestorId);
            $payload['my_profit_share'] = $participant['profit_share'] ?? null;
            $payload['my_share_percentage'] = $participant['share_percentage'] ?? null;
        }

        return $payload;
    }

    private function calculateProfit(?float $soldAmount, float $basePrice, float $refineryCost): ?float
    {
        if ($soldAmount === null) {
            return null;
        }

        return round($soldAmount - $basePrice - $refineryCost, 2);
    }

    private function resolveInvestorScope(Request $request, ?int $investorId = null): ?tbl_investor
    {
        $user = $request->user();

        if ($user->role === 'investor') {
            return tbl_investor::where('user_id', $user->id)->where('investor_status', true)->first();
        }

        if ($investorId) {
            return tbl_investor::where('investor_id', $investorId)->where('investor_status', true)->first();
        }

        return null;
    }
}
