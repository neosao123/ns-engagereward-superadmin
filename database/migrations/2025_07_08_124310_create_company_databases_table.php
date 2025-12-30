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
        Schema::create('company_databases', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id');
            $table->string('db_name');
            $table->string('db_username');
            $table->string('db_password');
            $table->string('db_host');
            $table->string('db_port');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_databases');
    }
};
