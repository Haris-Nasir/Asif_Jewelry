<?php

namespace App\Http\Controllers;

use App\Models\tbl_investor;
use App\Models\tbl_lab_job;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LabJobController extends Controller
{
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
        $query = tbl_lab_job::with('investor:investor_id,investor_name')
            ->where('lab_job_status', true)
            ->orderByDesc('job_date')
            ->orderByDesc('lab_job_id');

        $investor = $this->resolveInvestorScope($request, $validated['investor_id'] ?? null);
        if ($investor) {
            $query->where('investor_id', $investor->investor_id);
        }

        if (!empty($validated['from_date']) && !empty($validated['to_date'])) {
            $query->whereBetween('job_date', [$validated['from_date'], $validated['to_date']]);
        }

        if (!empty($validated['job_status'])) {
            $query->where('job_status', $validated['job_status']);
        }

        return response()->json($query->paginate($paginate));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_date' => 'required|date',
            'investor_id' => 'required|integer|exists:tbl_investors,investor_id',
            'job_reference' => 'nullable|string|max:50',
            'metal_type' => ['required', Rule::in(['gold', 'silver'])],
            'weight_grams' => 'required|numeric|min:0',
            'base_price' => 'required|numeric|min:0',
            'cash_amount' => 'nullable|numeric|min:0',
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

        $job = tbl_lab_job::create([
            'job_date' => $validated['job_date'],
            'investor_id' => $validated['investor_id'],
            'job_reference' => $validated['job_reference'] ?? null,
            'metal_type' => $validated['metal_type'],
            'weight_grams' => $validated['weight_grams'],
            'base_price' => $validated['base_price'],
            'cash_amount' => $validated['cash_amount'] ?? 0,
            'refinery_cost' => $validated['refinery_cost'] ?? 0,
            'sold_amount' => $soldAmount,
            'profit_amount' => $profit,
            'job_status' => $soldAmount !== null ? 'sold' : 'open',
            'notes' => $validated['notes'] ?? null,
            'created_by' => optional($request->user())->id,
            'lab_job_status' => true,
        ]);

        return response()->json([
            'status' => 1,
            'message' => 'Laboratory job created successfully.',
            'data' => $job->load('investor:investor_id,investor_name'),
        ]);
    }

    public function update(Request $request, int $labJobId)
    {
        $job = tbl_lab_job::where('lab_job_id', $labJobId)
            ->where('lab_job_status', true)
            ->firstOrFail();

        $validated = $request->validate([
            'job_date' => 'required|date',
            'investor_id' => 'required|integer|exists:tbl_investors,investor_id',
            'job_reference' => 'nullable|string|max:50',
            'metal_type' => ['required', Rule::in(['gold', 'silver'])],
            'weight_grams' => 'required|numeric|min:0',
            'base_price' => 'required|numeric|min:0',
            'cash_amount' => 'nullable|numeric|min:0',
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

        $job->update([
            'job_date' => $validated['job_date'],
            'investor_id' => $validated['investor_id'],
            'job_reference' => $validated['job_reference'] ?? null,
            'metal_type' => $validated['metal_type'],
            'weight_grams' => $validated['weight_grams'],
            'base_price' => $validated['base_price'],
            'cash_amount' => $validated['cash_amount'] ?? 0,
            'refinery_cost' => $validated['refinery_cost'] ?? 0,
            'sold_amount' => $soldAmount,
            'profit_amount' => $profit,
            'job_status' => $soldAmount !== null ? 'sold' : 'open',
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'status' => 1,
            'message' => 'Laboratory job updated successfully.',
            'data' => $job->fresh()->load('investor:investor_id,investor_name'),
        ]);
    }

    public function destroy(int $labJobId)
    {
        $job = tbl_lab_job::where('lab_job_id', $labJobId)
            ->where('lab_job_status', true)
            ->firstOrFail();

        $job->lab_job_status = false;
        $job->save();

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
            $query->where('investor_id', $investor->investor_id);
        }

        if (!empty($validated['from_date']) && !empty($validated['to_date'])) {
            $query->whereBetween('job_date', [$validated['from_date'], $validated['to_date']]);
        }

        $jobs = (clone $query)->get();
        $soldJobs = $jobs->where('job_status', 'sold');

        return response()->json([
            'total_jobs' => $jobs->count(),
            'open_jobs' => $jobs->where('job_status', 'open')->count(),
            'sold_jobs' => $soldJobs->count(),
            'total_weight_grams' => round($jobs->sum('weight_grams'), 3),
            'total_cash_amount' => round($jobs->sum('cash_amount'), 2),
            'total_lab_profit' => round($soldJobs->sum('profit_amount'), 2),
        ]);
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
