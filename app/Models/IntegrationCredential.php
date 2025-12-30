<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationCredential extends Model
{
    use HasFactory;
	
	protected $fillable = [
		'company_id',
		'social_media_id',
		'type',
		'value',
		'is_active',
		'created_at',
		'updated_at',
		'deleted_at'
	];
	
	public function company()
	{
		return $this->belongsTo(Company::class, 'company_id', 'id');
	}
	
	
	public function socialMedia()
	{
		return $this->belongsTo(SocialMediaApp::class, 'social_media_id', 'id');
	}
}
