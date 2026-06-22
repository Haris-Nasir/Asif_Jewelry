<?php

namespace App\Http\Controllers;

use App\Models\tbl_investor;
use App\Models\tbl_investor_transaction;
use App\Models\User;
use App\Services\InvestorProfitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Concerns\AuditsActions;

class InvestorController extends Controller
{
    use AuditsActions;

    protected InvestorProfitService $profitService;

    public function __construct(InvestorProfitService $profitService)
    {
        $this->profitService = $profitService;
    }

    public function summary(Request $request)
    {
        $validated = $request->validate([
            'period' => 'nullable|in:daily,monthly,quarterly,financial_year',
            'date' => 'nullable|date',
            'investor_id' => 'nullable|integer|exists:tbl_investors,investor_id',
        ]);

        $investor = $this->resolveInvestor($request, $validated['investor_id'] ?? null);
        if (!$investor) {
            return response()->json(['message' => 'Investor profile not found.'], 404);
        }

        $period = $validated['period'] ?? 'monthly';
        $summary = $this->profitService->buildSummary($investor, $period, $validated['date'] ?? null);

        $transactions = tbl_investor_transaction::where('investor_id', $investor->investor_id)
            ->where('transaction_status', true)
            ->whereBetween('transaction_date', [$summary['period']['from_date'], $summary['period']['to_date']])
            ->orderByDesc('transaction_date')
            ->orderByDesc('investor_transaction_id')
            ->limit(50)
            ->get();

        return response()->json([
            'investor' => $investor,
            'period' => $summary['period'],
            'profit_summary' => $summary['profit_summary'],
            'gold_holdings' => $summary['gold_holdings'],
            'transactions' => $transactions,
        ]);
    }

    public function index()
    {
        $investors = tbl_investor::with('user:id,name,email')
            ->where('investor_status', true)
            ->orderBy('investor_name')
            ->get();

        return response()->json(['data' => $investors]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'investor_name' => 'required|string|max:100',
            'contact_no' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:255',
            'profit_share_percentage' => 'required|numeric|min:0|max:100',
            'create_login' => 'nullable|boolean',
            'password' => 'nullable|string|min:6',
        ]);

        DB::beginTransaction();
        try {
            $userId = null;
            if (!empty($validated['create_login']) && !empty($validated['email'])) {
                $user = User::create([
                    'name' => $validated['investor_name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password'] ?? 'password'),
                    'role' => 'investor',
                ]);
                $userId = $user->id;
            }

            $investor = tbl_investor::create([
                'user_id' => $userId,
                'investor_name' => $validated['investor_name'],
                'contact_no' => $validated['contact_no'] ?? null,
                'email' => $validated['email'] ?? null,
                'profit_share_percentage' => $validated['profit_share_percentage'],
                'investor_status' => true,
            ]);

            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => 'Investor created successfully.',
                'data' => $investor,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => -1,
                'message' => $e->getMessage() ?: 'Failed to create investor.',
            ], 500);
        }
    }

    public function update(Request $request, int $investorId)
    {
        $investor = tbl_investor::findOrFail($investorId);

        $validated = $request->validate([
            'investor_name' => 'required|string|max:100',
            'contact_no' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:255',
            'profit_share_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $investor->update($validated);

        return response()->json([
            'status' => 1,
            'message' => 'Investor updated successfully.',
            'data' => $investor,
        ]);
    }

    public function transactions(Request $request)
    {
        $validated = $request->validate([
            'investor_id' => 'nullable|integer|exists:tbl_investors,investor_id',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'paginate' => 'nullable|integer|min:1|max:100',
        ]);

        $investor = $this->resolveInvestor($request, $validated['investor_id'] ?? null);
        if (!$investor) {
            return response()->json(['message' => 'Investor profile not found.'], 404);
        }

        $paginate = $validated['paginate'] ?? 20;
        $query = tbl_investor_transaction::where('investor_id', $investor->investor_id)
            ->where('transaction_status', true)
            ->orderByDesc('transaction_date')
            ->orderByDesc('investor_transaction_id');

        if (!empty($validated['from_date']) && !empty($validated['to_date'])) {
            $query->whereBetween('transaction_date', [$validated['from_date'], $validated['to_date']]);
        }

        return response()->json($query->paginate($paginate));
    }

    public function addTransaction(Request $request)
    {
        $validated = $request->validate([
            'investor_id' => 'required|integer|exists:tbl_investors,investor_id',
            'transaction_date' => 'required|date',
            'transaction_type' => ['required', Rule::in(['deposit', 'withdrawal', 'gold_buy', 'gold_sell'])],
            'metal_type' => ['nullable', Rule::in(['gold', 'silver'])],
            'weight_grams' => 'nullable|numeric|min:0',
            'rate_per_gram' => 'nullable|numeric|min:0',
            'amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $type = $validated['transaction_type'];

        if (in_array($type, ['gold_buy', 'gold_sell'], true)) {
            if (empty($validated['metal_type']) || empty($validated['weight_grams']) || empty($validated['rate_per_gram'])) {
                return response()->json([
                    'status' => -1,
                    'message' => 'Gold transactions require metal type, weight (g), and rate per gram.',
                ], 422);
            }
            $amount = round((float) $validated['weight_grams'] * (float) $validated['rate_per_gram'], 2);
        } else {
            if (empty($validated['amount']) || (float) $validated['amount'] <= 0) {
                return response()->json([
                    'status' => -1,
                    'message' => 'Deposit and withdrawal require a positive amount.',
                ], 422);
            }
            $amount = round((float) $validated['amount'], 2);
        }

        $transaction = tbl_investor_transaction::create([
            'investor_id' => $validated['investor_id'],
            'transaction_date' => $validated['transaction_date'],
            'transaction_type' => $type,
            'metal_type' => $validated['metal_type'] ?? null,
            'weight_grams' => $validated['weight_grams'] ?? null,
            'rate_per_gram' => $validated['rate_per_gram'] ?? null,
            'amount' => $amount,
            'notes' => $validated['notes'] ?? null,
            'created_by' => optional($request->user())->id,
            'transaction_status' => true,
        ]);

        $this->audit('create', 'investor_transaction', $transaction->investor_transaction_id, 'Investor transaction: ' . $type);

        return response()->json([
            'status' => 1,
            'message' => 'Transaction recorded successfully.',
            'data' => $transaction,
        ]);
    }

    public function deleteTransaction(int $transactionId)
    {
        $transaction = tbl_investor_transaction::where('investor_transaction_id', $transactionId)
            ->where('transaction_status', true)
            ->firstOrFail();

        $transaction->transaction_status = false;
        $transaction->save();

        return response()->json([
            'status' => 1,
            'message' => 'Transaction deleted successfully.',
        ]);
    }

    private function resolveInvestor(Request $request, ?int $investorId = null): ?tbl_investor
    {
        $user = $request->user();

        if ($user->role === 'investor') {
            return tbl_investor::where('user_id', $user->id)->where('investor_status', true)->first();
        }

        if ($investorId) {
            return tbl_investor::where('investor_id', $investorId)->where('investor_status', true)->first();
        }

        return tbl_investor::where('investor_status', true)->orderBy('investor_id')->first();
    }
}
