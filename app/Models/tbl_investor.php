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
        'investor_status',
    ];

    protected $casts = [
        'profit_share_percentage' => 'decimal:2',
        'investor_status' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
