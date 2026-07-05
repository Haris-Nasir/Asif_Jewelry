<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class tbl_inward_details extends Model
{
    use HasFactory;

    protected $primaryKey = 'inward_details_id';

    public function quality(){
        return $this->hasOne('App\Models\tbl_sell_quality', 'sell_quality_id', 'sell_quality_id')
            ->with('category:sell_quality_category_id,sell_category_name,metal_type');
    }
}
