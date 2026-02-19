<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySocialMediaSetting extends Model
{
    use HasFactory;
	
	protected $fillable = [
		'company_id',
		'social_media_app_id',
		'social_media_page_link',
		'social_media_operation',
		'is_active',
		'created_at',
		'updated_at',
	];
	
	public function company()
	{
		return $this->belongsTo(Company::class, 'company_id', 'id');
	}
	
	public function socialMediaApp()
	{
		return $this->belongsTo(SocialMediaApp::class, 'social_media_app_id', 'id');
	}
}
