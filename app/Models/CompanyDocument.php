<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyDocument extends Model
{
    use HasFactory;
	
	protected $fillable = [
		'company_id',
		'document_type',
		'document_number',
		'document_file',
		'is_active',
		'created_at',
		'updated_at',
		'deleted_at'
	];
	
	public function company()
	{
		return $this->belongsTo(Company::class, 'company_id', 'id');
	}
	
	
}
