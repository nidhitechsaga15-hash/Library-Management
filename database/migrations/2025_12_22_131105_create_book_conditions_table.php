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
        Schema::create('book_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->foreignId('reported_by')->constrained('users')->onDelete('cascade');
            $table->enum('condition_type', ['damaged', 'lost', 'missing', 'other'])->default('damaged');
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['reported', 'under_review', 'resolved', 'written_off'])->default('reported');
            $table->date('reported_date');
            $table->date('resolved_date')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_conditions');
    }
};
