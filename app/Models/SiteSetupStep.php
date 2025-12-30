<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; 

class SiteSetupStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'step_name',
        'created_at',
        'completed_at',
        'status',
        'request_data',
        'response_data',
        'order_no'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class,'company_id','id');
    }
}
