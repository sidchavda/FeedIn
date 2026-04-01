<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            if (!Schema::hasColumn('news', 'country_id')) {
                $table->foreignId('country_id')->nullable()->after('status')->constrained('countries')->onDelete('set null');
            }
            if (!Schema::hasColumn('news', 'state_id')) {
                $table->foreignId('state_id')->nullable()->after('country_id')->constrained('states')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            if (Schema::hasColumn('news', 'country_id')) {
                $table->dropForeign(['country_id']);
                $table->dropColumn('country_id');
            }
            if (Schema::hasColumn('news', 'state_id')) {
                $table->dropForeign(['state_id']);
                $table->dropColumn('state_id');
            }
        });
    }
};
