<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;
	
	protected $fillable = [
        'subscription_title',
		'discount_type',
		'discount_value',
        'subscription_months',
        'subscription_per_month_price',
        'subscription_total_price',
        'is_active',
        'from_date',
        'to_date',
		'currency_code'
    ];
	
	
	public static function filterSubscription(string $search = "", $limit = 0, $offset = 0)
	{
		$query = self::query()
			->select('subscriptions.*')
			->whereNull('deleted_at');

		// Global search (across multiple fields)
		$query->where(function ($query) use ($search) {
			$query->where('subscriptions.subscription_title', 'like', "%{$search}%")
				  ->orWhere('subscriptions.subscription_months', 'like', "%{$search}%")
				  ->orWhere('subscriptions.subscription_per_month_price', 'like', "%{$search}%")
				  ->orWhere('subscriptions.subscription_total_price', 'like', "%{$search}%")
				  ->orWhere('subscriptions.from_date', 'like', "%{$search}%")
				  ->orWhere('subscriptions.to_date', 'like', "%{$search}%");
		});

		$query->orderByDesc('subscriptions.id');

		$total = $query->count();

		if ($limit && $limit > 0) {
			$query->limit($limit)->offset($offset);
		}

		$result = $query->get();

		return [
			'totalRecords' => $total,
			'result' => $result
		];
	}
	
	
	public function subscriptionPlanSocialMedia()
	{
		return $this->hasMany(
			SubscriptionPlanSocialMedia::class,
			'subscription_id'
		);
	}
	

}
