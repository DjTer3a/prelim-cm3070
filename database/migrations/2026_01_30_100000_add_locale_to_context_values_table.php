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
        Schema::table('context_values', function (Blueprint $table) {
            $table->string('locale', 10)->default('en')->after('visibility');
            $table->unique(['context_id', 'profile_attribute_id', 'locale']);
        });

        Schema::table('context_values', function (Blueprint $table) {
            $table->dropUnique(['context_id', 'profile_attribute_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('context_values', function (Blueprint $table) {
            $table->unique(['context_id', 'profile_attribute_id']);
        });

        Schema::table('context_values', function (Blueprint $table) {
            $table->dropUnique(['context_id', 'profile_attribute_id', 'locale']);
            $table->dropColumn('locale');
        });
    }
};
