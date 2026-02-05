<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
			$table->string('company_code')->nullable();
            $table->string('company_key')->nullable();
            $table->string('company_name')->nullable();
            $table->string('legal_type')->nullable();
            $table->longText('description')->nullable();
            $table->string('industry_type')->nullable();
            $table->string('reg_number')->nullable();
            $table->string('gst_number')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('primary_contact_name')->nullable();
            $table->string('primary_contact_email')->nullable();
            $table->string('primary_contact_number')->nullable();
            $table->longText('office_address_line_one')->nullable();
            $table->longText('office_address_line_two')->nullable();
            $table->string('office_address_city')->nullable();
            $table->string('office_address_province_state')->nullable();
            $table->string('office_address_country_code')->nullable();
            $table->string('office_address_postal_code')->nullable();
            $table->boolean('is_billing_address_same')->default(false);
            $table->longText('billing_address_line_one')->nullable();
            $table->longText('billing_address_line_two')->nullable();
            $table->string('billing_address_city')->nullable();
            $table->string('billing_address_province_state')->nullable();
            $table->string('billing_address_country_code')->nullable();
            $table->string('billing_address_postal_code')->nullable();
            $table->string('account_status')->nullable();
            $table->string('company_logo')->nullable();
			$table->integer('subscription_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
			$table->string('company_country_code')->nullable();
            $table->string('password')->nullable();
			$table->string('phone_country')->nullable();
			$table->string('trade_name')->nullable();
			$table->boolean('setup_status')->default(true);
            $table->boolean('is_suspend')->default(false);
			$table->longText('reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
