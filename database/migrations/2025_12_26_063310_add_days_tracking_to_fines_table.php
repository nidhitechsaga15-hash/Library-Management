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
        Schema::table('fines', function (Blueprint $table) {
            $table->integer('days_paid')->default(0)->nullable()->after('payment_notes');
            $table->integer('days_overdue_at_creation')->nullable()->after('days_paid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fines', function (Blueprint $table) {
            $table->dropColumn(['days_paid', 'days_overdue_at_creation']);
        });
    }
};
