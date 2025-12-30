<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;
class SubscriptionPurchase extends Model
{
    use HasFactory;
	
	
	protected $fillable = [
	    'company_id',
	    'subscription_id',
        'subscription_title',
        'subscription_months',
        'subscription_per_month_price',
        'subscription_total_price',
		'subscription_purchase_id',
        'is_active',
        'from_date',
		'discount_type',
		'discount_value',
		'currency_code',
		'status',
        'to_date',
		'payment_status',
		'payment_order_id',
		'payment_response',
		'payment_id',
		'payment_mode',
		'webhook_response'
    ];
	
	public function company()
	{
		return $this->belongsTo(Company::class, 'company_id', 'id');
	}

	
	
}
