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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('mobile')->nullable();
            $table->string('father_name')->nullable();
            $table->string('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('disabilitiese')->nullable();
            $table->string('village')->nullable(); 
            $table->string('police_station')->nullable();
            $table->string('state')->nullable();
            $table->string('district')->nullable();
            $table->string('qualification')->nullable();
            $table->string('trade_one')->nullable();
            $table->string('category_one')->nullable();
            $table->string('trade_two')->nullable();
            $table->string('category_two')->nullable();
            $table->string('trade_three')->nullable();
            $table->string('category_three')->nullable();
            $table->string('job_status')->nullable();
            $table->string('current_company')->nullable();
            $table->string('current_site_location')->nullable();
            $table->string('current_job_role')->nullable();
            $table->string('current_start_date')->nullable();
            $table->string('current_end_date')->nullable();
            $table->string('current_skill_type')->nullable();
            $table->string('previous_company')->nullable();
            $table->string('previous_site_location')->nullable();
            $table->string('previous_job_role')->nullable();
            $table->string('previous_start_date')->nullable();
            $table->string('previous_end_date')->nullable();
            $table->string('previous_skill_type')->nullable();
            $table->string('pf')->nullable();
            $table->string('esi')->nullable();
            $table->string('bo_card')->nullable();
            $table->string('ayush_card')->nullable();
            $table->string('addhar_number')->nullable();
            $table->string('front_addhar_img')->nullable();
            $table->string('back_addhar_img')->nullable();
            $table->string('pan_number')->nullable();
            $table->string('pan_img')->nullable();
            $table->string('loan')->nullable();
            $table->string('amount')->nullable();
            $table->string('institution')->nullable();
            $table->string('tenure')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('branch')->nullable();
            $table->string('account_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
