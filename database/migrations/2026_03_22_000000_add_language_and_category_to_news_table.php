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
        Schema::table('news', function (Blueprint $table) {
            if (!Schema::hasColumn('news', 'language_id')) {
                $table->foreignId('language_id')->nullable()->after('link')->constrained('languages')->onDelete('set null');
            }
            if (!Schema::hasColumn('news', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('language_id')->constrained('categories')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            if (Schema::hasColumn('news', 'language_id')) {
                $table->dropForeign(['language_id']);
                $table->dropColumn('language_id');
            }
            if (Schema::hasColumn('news', 'category_id')) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            }
        });
    }
};