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
            $table->foreignId('library_id')->nullable()->after('category_id')->constrained()->onDelete('set null');
            $table->string('almirah')->nullable()->after('rack_number');
            $table->string('row')->nullable()->after('almirah');
            $table->string('qr_code')->nullable()->after('row');
            $table->string('barcode')->nullable()->after('qr_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropForeign(['library_id']);
            $table->dropColumn(['library_id', 'almirah', 'row', 'qr_code', 'barcode']);
        });
    }
};
