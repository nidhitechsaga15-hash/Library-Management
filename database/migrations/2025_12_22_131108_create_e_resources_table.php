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
        Schema::create('e_resources', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('author_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('library_id')->constrained()->onDelete('cascade');
            $table->string('file_path');
            $table->string('file_type')->nullable(); // pdf, epub, etc.
            $table->integer('file_size')->nullable(); // in bytes
            $table->string('isbn')->nullable();
            $table->string('publisher')->nullable();
            $table->integer('publication_year')->nullable();
            $table->enum('access_level', ['public', 'member', 'restricted'])->default('member');
            $table->json('allowed_roles')->nullable(); // ['student', 'staff'] for restricted access
            $table->integer('download_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('e_resources');
    }
};
