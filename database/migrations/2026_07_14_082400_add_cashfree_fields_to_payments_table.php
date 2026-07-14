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
        Schema::table('payments', function (Blueprint $table) {
            // Cashfree Order ID created before payment (e.g. order_123abc)
            $table->string('cashfree_order_id')->nullable()->after('razorpay_payment_id');
            // Cashfree Payment ID returned after successful payment (e.g. pay_123abc)
            $table->string('cashfree_payment_id')->nullable()->after('cashfree_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['cashfree_order_id', 'cashfree_payment_id']);
        });
    }
};
