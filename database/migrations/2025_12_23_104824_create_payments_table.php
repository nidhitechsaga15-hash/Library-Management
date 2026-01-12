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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('payment_type'); // fine, membership, e_resource
            $table->string('paymentable_type')->nullable(); // Fine, EResource, etc.
            $table->unsignedBigInteger('paymentable_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('INR');
            $table->string('payment_method')->default('razorpay'); // razorpay, stripe, etc.
            $table->string('payment_id')->unique()->nullable(); // Gateway payment ID
            $table->string('order_id')->unique()->nullable(); // Gateway order ID
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded'])->default('pending');
            $table->text('gateway_response')->nullable(); // JSON response from gateway
            $table->text('failure_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->index(['paymentable_type', 'paymentable_id']);
            $table->index('payment_id');
            $table->index('order_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
