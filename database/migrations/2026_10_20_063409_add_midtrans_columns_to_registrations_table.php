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
        Schema::table('registrations', function (Blueprint $table) {
            // Snap transaction created right after registration; the redirect_url is the payment page
            $table->string('snap_token', 100)->nullable()->after('payment_status');
            $table->string('snap_redirect_url', 255)->nullable()->after('snap_token');
            // Filled from the Midtrans notification (settlement/capture)
            $table->string('payment_method', 50)->nullable()->after('snap_redirect_url');
            $table->dateTime('paid_at')->nullable()->after('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn(['snap_token', 'snap_redirect_url', 'payment_method', 'paid_at']);
        });
    }
};
