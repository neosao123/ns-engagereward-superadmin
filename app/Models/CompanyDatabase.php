<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class CompanyDatabase extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'db_name',
        'db_username',
        'db_password',
        'db_host',
        'db_port',
    ];
    
    public function patasanstha()
    {
        return $this->belongsTo(Company::class,'company_id','id');
    }
}
