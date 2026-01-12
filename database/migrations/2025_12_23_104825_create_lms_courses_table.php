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
        Schema::create('lms_courses', function (Blueprint $table) {
            $table->id();
            $table->string('course_code')->unique();
            $table->string('course_name');
            $table->text('description')->nullable();
            $table->string('department')->nullable();
            $table->integer('semester')->nullable();
            $table->string('year')->nullable();
            $table->string('batch')->nullable();
            $table->json('subjects')->nullable(); // Array of subjects
            $table->json('recommended_books')->nullable(); // Array of book IDs
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['department', 'semester']);
            $table->index('course_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_courses');
    }
};
