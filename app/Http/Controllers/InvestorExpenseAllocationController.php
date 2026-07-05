<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuditsActions;
use App\Models\tbl_expense;
use App\Models\tbl_investor;
use App\Models\tbl_investor_expense_allocation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvestorExpenseAllocationController extends Controller
{
    use AuditsActions;

    public function index(Request $request)
    {
        $paginate = (int) $request->input('paginate', 15);
        $fromDate = $request->input('fromdate', Carbon::now()->subDays(30)->toDateString());
        $toDate = $request->input('todate', Carbon::now()->toDateString());
        $investorId = $request->input('investor_id');

        $query = tbl_investor_expense_allocation::join('tbl_investors', 'tbl_investor_expense_allocations.investor_id', '=', 'tbl_investors.investor_id')
            ->leftJoin('tbl_expenses', 'tbl_investor_expense_allocations.expense_id', '=', 'tbl_expenses.expense_id')
            ->where('tbl_investor_expense_allocations.allocation_status', true)
            ->whereDate('tbl_investor_expense_allocations.allocation_date', '>=', $fromDate)
            ->whereDate('tbl_investor_expense_allocations.allocation_date', '<=', $toDate)
            ->when($investorId, function ($q) use ($investorId) {
                $q->where('tbl_investor_expense_allocations.investor_id', $investorId);
            })
            ->orderByDesc('tbl_investor_expense_allocations.allocation_date')
            ->orderByDesc('tbl_investor_expense_allocations.investor_expense_allocation_id')
            ->select(
                'tbl_investor_expense_allocations.investor_expense_allocation_id',
                'tbl_investor_expense_allocations.investor_id',
                'tbl_investors.investor_name',
                'tbl_investor_expense_allocations.expense_id',
                'tbl_expenses.expense_description',
                'tbl_investor_expense_allocations.allocation_date',
                'tbl_investor_expense_allocations.description',
                'tbl_investor_expense_allocations.allocated_amount',
                'tbl_investor_expense_allocations.notes'
            );

        return response()->json($query->paginate($paginate));
    }

    public function expensesForAllocation(Request $request)
    {
        $fromDate = $request->input('fromdate', Carbon::now()->subDays(365)->toDateString());
        $toDate = $request->input('todate', Carbon::now()->addDays(90)->toDateString());

        $expenses = tbl_expense::join('tbl_expense_categories', 'tbl_expenses.expense_category_id', '=', 'tbl_expense_categories.expense_category_id')
            ->where('tbl_expenses.expense_status', true)
            ->whereDate('tbl_expenses.expense_date', '>=', $fromDate)
            ->whereDate('tbl_expenses.expense_date', '<=', $toDate)
            ->orderByDesc('tbl_expenses.expense_date')
            ->select(
                'tbl_expenses.expense_id',
                'tbl_expenses.expense_date',
                'tbl_expenses.expense_description',
                'tbl_expenses.expense_amount',
                'tbl_expense_categories.expense_category'
            )
            ->limit(200)
            ->get();

        $data = $expenses->map(function ($expense) {
            $allocated = tbl_investor_expense_allocation::allocatedTotalForExpense((int) $expense->expense_id);
            $remaining = round((float) $expense->expense_amount - $allocated, 2);

            return [
                'expense_id' => $expense->expense_id,
                'expense_date' => Carbon::parse($expense->getAttributes()['expense_date'])->format('d-m-Y'),
                'expense_description' => $expense->expense_description,
                'expense_category' => $expense->expense_category,
                'expense_amount' => (float) $expense->expense_amount,
                'allocated_amount' => $allocated,
                'remaining_amount' => max($remaining, 0),
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'allocation_date' => 'required|date',
            'description' => 'required|string|max:255',
            'expense_id' => 'nullable|integer|exists:tbl_expenses,expense_id',
            'notes' => 'nullable|string|max:500',
            'allocations' => 'required|array|min:1',
            'allocations.*.investor_id' => 'required|integer|exists:tbl_investors,investor_id',
            'allocations.*.amount' => 'required|numeric|min:0.01',
        ]);

        $rows = collect($validated['allocations'])->filter(function ($row) {
            return (float) $row['amount'] > 0;
        });

        if ($rows->isEmpty()) {
            return response()->json([
                'status' => -1,
                'message' => 'Enter at least one investor with a positive amount.',
            ], 422);
        }

        $expense = null;
        $totalAmount = round($rows->sum('amount'), 2);

        if (!empty($validated['expense_id'])) {
            $expense = tbl_expense::where('expense_id', $validated['expense_id'])
                ->where('expense_status', true)
                ->first();

            if (!$expense) {
                return response()->json([
                    'status' => -1,
                    'message' => 'Linked expense not found.',
                ], 404);
            }

            $alreadyAllocated = tbl_investor_expense_allocation::allocatedTotalForExpense((int) $expense->expense_id);
            $remaining = round((float) $expense->expense_amount - $alreadyAllocated, 2);

            if ($totalAmount > $remaining + 0.001) {
                return response()->json([
                    'status' => -1,
                    'message' => 'Total distribution (Rs. ' . number_format($totalAmount, 2) . ') exceeds remaining expense amount (Rs. ' . number_format($remaining, 2) . ').',
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            $created = [];
            foreach ($rows as $row) {
                $investor = tbl_investor::where('investor_id', $row['investor_id'])
                    ->where('investor_status', true)
                    ->first();

                if (!$investor) {
                    throw new \RuntimeException('Investor not found or inactive.');
                }

                $allocation = tbl_investor_expense_allocation::create([
                    'investor_id' => $row['investor_id'],
                    'expense_id' => $validated['expense_id'] ?? null,
                    'allocation_date' => $validated['allocation_date'],
                    'description' => $validated['description'],
                    'allocated_amount' => round((float) $row['amount'], 2),
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => optional($request->user())->id,
                    'allocation_status' => true,
                ]);

                $created[] = $allocation;
            }

            DB::commit();

            $this->audit(
                'create',
                'investor_expense_allocation',
                $created[0]->investor_expense_allocation_id,
                'Expense distributed: Rs. ' . $totalAmount . ' to ' . count($created) . ' investor(s)'
            );

            return response()->json([
                'status' => 1,
                'message' => 'Expense distributed successfully.',
                'data' => $created,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => -1,
                'message' => $e->getMessage() ?: 'Failed to distribute expense.',
            ], 500);
        }
    }

    public function update(Request $request, int $allocationId)
    {
        $allocation = tbl_investor_expense_allocation::where('investor_expense_allocation_id', $allocationId)
            ->where('allocation_status', true)
            ->firstOrFail();

        $validated = $request->validate([
            'allocation_date' => 'required|date',
            'investor_id' => 'required|integer|exists:tbl_investors,investor_id',
            'description' => 'required|string|max:255',
            'expense_id' => 'nullable|integer|exists:tbl_expenses,expense_id',
            'allocated_amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        if (!empty($validated['expense_id'])) {
            $expense = tbl_expense::where('expense_id', $validated['expense_id'])
                ->where('expense_status', true)
                ->firstOrFail();

            $alreadyAllocated = tbl_investor_expense_allocation::allocatedTotalForExpense(
                (int) $expense->expense_id,
                $allocationId
            );
            $remaining = round((float) $expense->expense_amount - $alreadyAllocated, 2);

            if ((float) $validated['allocated_amount'] > $remaining + 0.001) {
                return response()->json([
                    'status' => -1,
                    'message' => 'Amount exceeds remaining expense balance (Rs. ' . number_format($remaining, 2) . ').',
                ], 422);
            }
        }

        $allocation->update([
            'allocation_date' => $validated['allocation_date'],
            'investor_id' => $validated['investor_id'],
            'description' => $validated['description'],
            'expense_id' => $validated['expense_id'] ?? null,
            'allocated_amount' => round((float) $validated['allocated_amount'], 2),
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'status' => 1,
            'message' => 'Allocation updated successfully.',
            'data' => $allocation,
        ]);
    }

    public function destroy(int $allocationId)
    {
        $allocation = tbl_investor_expense_allocation::where('investor_expense_allocation_id', $allocationId)
            ->where('allocation_status', true)
            ->firstOrFail();

        $allocation->allocation_status = false;
        $allocation->save();

        return response()->json([
            'status' => 1,
            'message' => 'Allocation deleted successfully.',
        ]);
    }
}
