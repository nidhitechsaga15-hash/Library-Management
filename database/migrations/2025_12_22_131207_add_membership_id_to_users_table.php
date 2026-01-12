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
        Schema::table('users', function (Blueprint $table) {
            $table->string('membership_id')->unique()->nullable()->after('id');
            $table->enum('member_type', ['student', 'faculty', 'staff'])->nullable()->after('role');
            $table->enum('membership_status', ['active', 'suspended', 'expired'])->default('active')->after('is_active');
            $table->date('membership_expiry_date')->nullable()->after('membership_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['membership_id', 'member_type', 'membership_status', 'membership_expiry_date']);
        });
    }
};
