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
            $table->enum('condition_status', ['good', 'fair', 'damaged', 'lost', 'missing'])->default('good')->after('status');
            $table->date('condition_updated_at')->nullable()->after('condition_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['condition_status', 'condition_updated_at']);
        });
    }
};
