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
            if (!Schema::hasColumn('books', 'year')) {
                $table->string('year')->nullable()->after('semester');
            }
            if (!Schema::hasColumn('books', 'batch')) {
                $table->string('batch')->nullable()->after('year');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            if (Schema::hasColumn('books', 'year')) {
                $table->dropColumn('year');
            }
            if (Schema::hasColumn('books', 'batch')) {
                $table->dropColumn('batch');
            }
        });
    }
};
