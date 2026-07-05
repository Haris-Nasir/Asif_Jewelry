<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tbl_investor_expense_allocation extends Model
{
    use HasFactory;

    protected $table = 'tbl_investor_expense_allocations';
    protected $primaryKey = 'investor_expense_allocation_id';

    protected $fillable = [
        'investor_id',
        'expense_id',
        'allocation_date',
        'description',
        'allocated_amount',
        'notes',
        'created_by',
        'allocation_status',
    ];

    public function investor()
    {
        return $this->belongsTo(tbl_investor::class, 'investor_id', 'investor_id');
    }

    public function expense()
    {
        return $this->belongsTo(tbl_expense::class, 'expense_id', 'expense_id');
    }

    public static function allocatedTotalForExpense(int $expenseId, ?int $excludeAllocationId = null): float
    {
        $query = static::where('expense_id', $expenseId)
            ->where('allocation_status', true);

        if ($excludeAllocationId) {
            $query->where('investor_expense_allocation_id', '!=', $excludeAllocationId);
        }

        return (float) $query->sum('allocated_amount');
    }
}
