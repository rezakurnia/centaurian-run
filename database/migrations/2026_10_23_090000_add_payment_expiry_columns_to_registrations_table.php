<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            // Midtrans order_id of the current Snap attempt (registration number, plus a suffix on retries)
            $table->string('payment_order_id', 50)->nullable()->after('snap_redirect_url');
            // When the current Snap page stops working; past it the registration is released
            $table->dateTime('payment_expires_at')->nullable()->after('payment_order_id');
            // Set when an unpaid registration was cancelled because its payment lapsed (so it can be paid again)
            $table->dateTime('payment_expired_at')->nullable()->after('payment_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn(['payment_order_id', 'payment_expires_at', 'payment_expired_at']);
        });
    }
};
