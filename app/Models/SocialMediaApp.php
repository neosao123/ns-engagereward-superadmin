<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialMediaApp extends Model
{
    use HasFactory;
	
	protected $fillable = [
		'app_name',
		'app_icon',
		'is_active',
		'created_at',
		'updated_at',
		'deleted_at'
	];
	
	public function companySocialMediaSettings()
	{
		return $this->hasMany(CompanySocialMediaSetting::class, 'social_media_app_id', 'id');
	}
	
	
	public static function filterData(string $searchTerm = "", int $limit = 0, int $skip = 0)
	{
		$query = self::whereNull('deleted_at');

		// Apply search filter if provided
		if ($searchTerm) {
			$query->where(function ($query) use ($searchTerm) {
				$query->where('app_name', 'like', "%{$searchTerm}%");
			});
		}

		// Get the total count of records before applying pagination
		$total = $query->count();

		// Apply pagination if limit is provided
		if ($limit > 0) {
			$query->limit($limit)->offset($skip);
		}

		$result = $query->orderBy('id', 'DESC')->get();

		return ["totalRecords" => $total, "result" => $result];
	}
}
