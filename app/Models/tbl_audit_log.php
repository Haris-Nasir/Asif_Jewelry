<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class tbl_audit_log extends Model
{
    protected $table = 'tbl_audit_logs';
    protected $primaryKey = 'audit_log_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'module',
        'record_id',
        'description',
        'metadata',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
