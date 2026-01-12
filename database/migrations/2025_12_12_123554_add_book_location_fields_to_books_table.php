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
            // Add book serial number for location tracking
            $table->string('book_serial')->nullable()->after('row');
            // Rename existing fields for consistency (optional - keeping both for backward compatibility)
            // We'll use almirah_no, row_no as aliases in model accessors
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('book_serial');
        });
    }
};
