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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();			
            $table->string('subscription_title')->nullable();
			$table->integer('subscription_months')->nullable();
			$table->decimal('subscription_per_month_price', 20, 2)->nullable();
			$table->decimal('subscription_total_price', 20, 2)->nullable();
			$table->string('discount_type')->nullable();
			$table->decimal('discount_value',20,2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->dateTime('from_date')->nullable(); 
            $table->dateTime('to_date')->nullable(); 
			$table->string('currency_code')->nullable();
            $table->timestamps();
			$table->softDeletes();
			
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
