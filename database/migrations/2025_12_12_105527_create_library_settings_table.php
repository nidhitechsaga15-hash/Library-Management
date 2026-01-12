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
        Schema::create('library_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_id')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('book_issue_duration_days')->default(14);
            $table->integer('book_collection_deadline_days')->default(2);
            $table->integer('max_books_per_student')->default(2);
            $table->integer('max_books_per_subject')->default(1);
            $table->decimal('fine_per_day', 8, 2)->default(5.00);
            $table->json('almirah_config')->nullable(); // Store almirah/row configurations
            $table->timestamps();
            
            $table->unique('library_id'); // One setting per library
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_settings');
    }
};
