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
        Schema::create('context_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('context_id')->constrained()->cascadeOnDelete();
            $table->foreignId('profile_attribute_id')->constrained()->cascadeOnDelete();
            $table->json('value');
            $table->enum('visibility', ['public', 'protected', 'private'])->default('private');
            $table->timestamps();

            $table->unique(['context_id', 'profile_attribute_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('context_values');
    }
};
