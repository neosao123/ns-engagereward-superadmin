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
        Schema::create('site_setup_steps', function (Blueprint $table) {
            $table->id('id');
            $table->bigInteger('company_id');
            $table->string('step_name');
            $table->dateTime('completed_at')->nullable();
            $table->string('status')->default('pending')->comment('pending, in_progress, complete');
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->tinyInteger('order_no')->default('0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_setup_steps');
    }
};
