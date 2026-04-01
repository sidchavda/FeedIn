<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 3)->unique();
            $table->string('iso3', 3)->nullable();
            $table->string('phone_code', 10)->nullable();
            $table->string('currency', 3)->nullable();
            $table->string('currency_symbol', 10)->nullable();
            $table->string('capital', 100)->nullable();
            $table->string('region', 100)->nullable();
            $table->string('subregion', 100)->nullable();
            $table->string('latitude', 20)->nullable();
            $table->string('longitude', 20)->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
