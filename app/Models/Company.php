<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

	protected $fillable = [
	    'company_code',
		'company_key',
		'company_name',
		'legal_type',
		'trade_name',
		'company_country_code',
		'description',
		'industry_type',
		'reg_number',
		'gst_number',
		'email',
		'phone',
		'phone_country',
		'website',
		'primary_contact_name',
		'primary_contact_email',
		'primary_contact_number',

		// Office Address
		'office_address_line_one',
		'office_address_line_two',
		'office_address_city',
		'office_address_province_state',
		'office_address_country_code',
		'office_address_postal_code',

		// Billing Address
		'is_billing_address_same',
		'billing_address_line_one',
		'billing_address_line_two',
		'billing_address_city',
		'billing_address_province_state',
		'billing_address_country_code',
		'billing_address_postal_code',

		'subscription_id',

		// Other
		'account_status',
		'company_logo',
		'reason',
		'is_active',
		'is_verified',
		'created_at',
		'updated_at',
		'deleted_at',
		'password',
		'company_country_code',
		'phone_country',
		'trade_name',
		'setup_status',
		'company_unique_code'
	];

	public static function filterCompany(
		string $search = "",
		int $limit = 0,
		int $offset = 0,
		string $company_key = "",
		string $company_name = "",
		string $email = "",
		string $phone = "",
		string $type = ""
	) {
		$query = self::query();
        $query->whereNull('deleted_at');
		if ($company_key !== "") {
			$query->where('id', $company_key);
		}
		if ($company_name !== "") {
			$query->where('id', 'like', "%{$company_name}%");
		}
		if ($email !== "") {
			$query->where('id', 'like', "%{$email}%");
		}
		if ($phone !== "") {
			$query->where('id', 'like', "%{$phone}%");
		}

		if ($search !== "") {
			$query->where(function ($q) use ($search) {
				$q->where('company_key', 'like', "%{$search}%")
				  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('company_code', 'like', "%{$search}%")
				  ->orWhere('email', 'like', "%{$search}%")
				  ->orWhere('phone', 'like', "%{$search}%");
			});
		}

		$total = $query->count();


		$query->orderBy('id', 'desc');

		if ($limit > 0) {
			$query->limit($limit)->offset($offset);
		}

		$result = $query->get();

		return ["totalRecords" => $total, "result" => $result];
	}

	public function socialMediaSettings()
	{
		return $this->hasMany(CompanySocialMediaSetting::class, 'company_id', 'id');
	}

	public function officeCountry()
    {
        return $this->belongsTo(Country::class, 'office_address_country_code', 'country_short_code');
    }

    // Billing Address Country Relationship
    public function billingCountry()
    {
        return $this->belongsTo(Country::class, 'billing_address_country_code', 'country_short_code');
    }

	public function companyDocument()
	{
		return $this->hasMany(CompanyDocument::class, 'company_id', 'id')->where('is_active', 1);
	}

	public function companyCountry()
    {
        return $this->belongsTo(Country::class, 'company_country_code', 'country_short_code');
    }

	public function steps()
	{
		return $this->hasMany(SiteSetupStep::class,'company_id','id');
	}


	/*public function subscriptionPurchases()
	{
		return $this->hasMany(SubscriptionPurchase::class,'company_id'->orderBy('id');
	}*/


	public function subscriptionPurchases()
	{
		return $this->hasOne(SubscriptionPurchase::class, 'company_id')->where("is_active",1)->where("status","active")->where("payment_status","paid");
	}

}
