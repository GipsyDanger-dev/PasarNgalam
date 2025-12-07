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
        Schema::table('orders', function (Blueprint $table) {
            // Add payment_status: 'pending', 'paid', 'failed', 'expired'
            $table->string('payment_status')->default('pending')->after('status');
            
            // Add payment method info
            $table->string('payment_method')->nullable()->after('payment_status');
            
            // Add payment verification code
            $table->string('payment_code')->nullable()->unique()->after('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'payment_method', 'payment_code']);
        });
    }
};
