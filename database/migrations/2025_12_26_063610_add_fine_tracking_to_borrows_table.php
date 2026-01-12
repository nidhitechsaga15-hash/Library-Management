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
        Schema::table('borrows', function (Blueprint $table) {
            $table->date('last_fine_paid_date')->nullable()->after('fine_per_day');
            $table->decimal('total_fine_paid', 10, 2)->default(0)->after('last_fine_paid_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrows', function (Blueprint $table) {
            $table->dropColumn(['last_fine_paid_date', 'total_fine_paid']);
        });
    }
};
