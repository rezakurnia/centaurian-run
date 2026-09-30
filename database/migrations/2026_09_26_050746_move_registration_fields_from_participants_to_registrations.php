<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn(['registration_number', 'sequence_number', 'barcode']);
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->string('registration_number', 20)->unique()->after('id');
            $table->integer('sequence_number')->after('registration_number');
            $table->string('barcode', 50)->unique()->after('sequence_number');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropUnique(['registration_number']);
            $table->dropUnique(['barcode']);
            $table->dropColumn(['registration_number', 'sequence_number', 'barcode']);
        });

        Schema::table('participants', function (Blueprint $table) {
            $table->string('registration_number', 20)->after('id');
            $table->integer('sequence_number')->after('registration_number');
            $table->string('barcode', 50)->after('sequence_number');
        });
    }
};