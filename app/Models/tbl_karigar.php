<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class tbl_karigar extends Model
{
    protected $table = 'tbl_karigars';
    protected $primaryKey = 'karigar_id';

    protected $fillable = [
        'karigar_name',
        'contact_no',
        'address',
        'karigar_status',
    ];

    public function setKarigarNameAttribute($value)
    {
        $value = preg_replace('/\s+/', ' ', trim((string) $value));
        $this->attributes['karigar_name'] = ucwords(strtolower($value));
    }

    public function jobs()
    {
        return $this->hasMany(tbl_karigar_job::class, 'karigar_id', 'karigar_id')
            ->where('karigar_job_status', true);
    }
}
