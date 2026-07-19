<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class tbl_investor extends Model
{
    protected $table = 'tbl_investors';
    protected $primaryKey = 'investor_id';

    protected $fillable = [
        'user_id',
        'investor_name',
        'contact_no',
        'email',
        'profit_share_percentage',
        'profit_split_mode',
        'investor_status',
    ];

    protected $casts = [
        'profit_share_percentage' => 'decimal:2',
        'investor_status' => 'boolean',
    ];

    public function usesCustomProfitSplit(): bool
    {
        return ($this->profit_split_mode ?? 'investment') === 'custom';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(tbl_investor_transaction::class, 'investor_id', 'investor_id');
    }

    public function labJobs()
    {
        return $this->hasMany(tbl_lab_job::class, 'investor_id', 'investor_id');
    }
}
