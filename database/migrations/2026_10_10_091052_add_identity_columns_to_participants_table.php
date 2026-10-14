<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->string('nip', 30)->nullable()->after('full_name');
            $table->string('nis', 30)->nullable()->after('nip');
            $table->string('sub_category', 30)->nullable()->after('gender');
            $table->string('unit', 50)->nullable()->after('sub_category');
            $table->string('class', 30)->nullable()->after('unit');
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn(['nip', 'nis', 'sub_category', 'unit', 'class']);
        });
    }
};