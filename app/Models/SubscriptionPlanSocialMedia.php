<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlanSocialMedia extends Model
{
    use HasFactory;
	
	protected $table="subscription_plan_social_media";
	protected $fillable = [

	    'subscription_id',
        'social_media_id'
    ];
	
	public function subscription()
	{
		return $this->belongsTo(Subscription::class, 'subscription_id');
	}
	
	public function socialMediaApp()
	{
		return $this->belongsTo(SocialMediaApp::class, 'social_media_id');
	}
	
}
