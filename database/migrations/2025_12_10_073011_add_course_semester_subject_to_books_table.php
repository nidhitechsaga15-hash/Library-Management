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
        Schema::table('books', function (Blueprint $table) {
            if (!Schema::hasColumn('books', 'course')) {
                $table->string('course')->nullable()->after('category_id');
            }
            if (!Schema::hasColumn('books', 'semester')) {
                $table->string('semester')->nullable()->after('course');
            }
            if (!Schema::hasColumn('books', 'subject')) {
                $table->string('subject')->nullable()->after('semester');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            if (Schema::hasColumn('books', 'course')) {
                $table->dropColumn('course');
            }
            if (Schema::hasColumn('books', 'semester')) {
                $table->dropColumn('semester');
            }
            if (Schema::hasColumn('books', 'subject')) {
                $table->dropColumn('subject');
            }
        });
    }
};
