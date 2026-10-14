<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->string('position', 50)->nullable()->after('unit');
            $table->string('child_name', 100)->nullable()->after('position');
            $table->string('child_class', 30)->nullable()->after('child_name');
            $table->integer('graduation_year')->nullable()->after('child_class');
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn(['position', 'child_name', 'child_class', 'graduation_year']);
        });
    }
};