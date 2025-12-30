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
        Schema::create('integration_credentials', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('company_id');
			$table->unsignedBigInteger('social_media_id');
			$table->longText('type')->nullable();
			$table->longText('value')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
			$table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_credentials');
    }
};
