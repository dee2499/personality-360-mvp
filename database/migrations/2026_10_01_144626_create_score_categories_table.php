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
        Schema::create('score_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('min_percentage', 5, 2)->default(0.00);
            $table->decimal('max_percentage', 5, 2);
            $table->string('emoji', 32)->default('🎯');
            $table->string('color', 16)->default('#4F46E5'); // Hex color code
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('score_categories');
    }
};
