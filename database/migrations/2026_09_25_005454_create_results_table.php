<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->unique()->constrained('registrations');
            $table->foreignId('participant_id')->unique()->constrained('participants');
            $table->foreignId('event_id')->constrained('events');
            $table->dateTime('start_time')->nullable();
            $table->dateTime('finish_time')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('scan_status', ['valid', 'invalid', 'duplicate'])->default('valid');
            $table->foreignId('scanned_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
