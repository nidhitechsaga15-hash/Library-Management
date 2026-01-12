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
        // Modify the enum to include 'waived' and 'adjusted'
        DB::statement("ALTER TABLE fines MODIFY COLUMN status ENUM('pending', 'paid', 'waived', 'adjusted') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE fines MODIFY COLUMN status ENUM('pending', 'paid') DEFAULT 'pending'");
    }
};
