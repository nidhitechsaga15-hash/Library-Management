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
        Schema::table('book_requests', function (Blueprint $table) {
            $table->timestamp('collection_deadline')->nullable()->after('approved_at');
            $table->boolean('stock_deducted')->default(false)->after('collection_deadline');
            $table->timestamp('stock_deducted_at')->nullable()->after('stock_deducted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('book_requests', function (Blueprint $table) {
            $table->dropColumn(['collection_deadline', 'stock_deducted', 'stock_deducted_at']);
        });
    }
};
