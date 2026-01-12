<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('book_requests', function (Blueprint $table) {
            // Add hold expiration timestamp
            $table->timestamp('hold_expires_at')->nullable()->after('collection_deadline');
            // Add received timestamp (when student actually picks up the book)
            $table->timestamp('received_at')->nullable()->after('hold_expires_at');
        });

        // Update status enum to include 'hold' and 'cancelled'
        DB::statement("ALTER TABLE book_requests MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'issued', 'hold', 'cancelled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('book_requests', function (Blueprint $table) {
            $table->dropColumn(['hold_expires_at', 'received_at']);
        });

        // Revert status enum
        DB::statement("ALTER TABLE book_requests MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'issued') DEFAULT 'pending'");
    }
};
