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
        Schema::create('member_type_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('member_type', ['student', 'faculty', 'staff'])->unique();
            $table->integer('max_books_allowed')->default(2);
            $table->integer('issue_duration_days')->default(14);
            $table->decimal('fine_per_day', 8, 2)->default(10.00);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_type_settings');
    }
};
