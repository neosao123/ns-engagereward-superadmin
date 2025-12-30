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
        Schema::create('subscription_purchases', function (Blueprint $table) {
            $table->id();
			$table->integer('subscription_id')->nullable();
			$table->integer('subscription_purchase_id')->nullable();
			$table->integer('company_id')->nullable();
			$table->integer('company_subscription_id')->nullable();
			$table->string('subscription_title')->nullable();
			$table->integer('subscription_months')->nullable();
			$table->decimal('subscription_per_month_price', 20, 2)->nullable();
			$table->decimal('subscription_total_price', 20, 2)->nullable();
            $table->string('discount_type')->nullable();
			$table->decimal('discount_value',20,2)->default(0.00);
			$table->string('currency_code')->nullable();
			$table->boolean('is_active')->default(true);
			$table->string('status')->default("active");
            $table->dateTime('from_date')->nullable(); 
            $table->dateTime('to_date')->nullable(); 
			$table->string('payment_status')->nullable();
            $table->string('payment_order_id')->nullable();
            $table->longText('payment_response')->nullable();
            $table->string('payment_id')->nullable();
            $table->string('payment_mode')->nullable();
            $table->longText('webhook_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_purchases');
    }
};
