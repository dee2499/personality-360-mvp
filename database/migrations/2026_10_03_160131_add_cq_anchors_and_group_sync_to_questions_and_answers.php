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
        Schema::table('questions', function (Blueprint $table) {
            $table->string('type')->default('individual')->after('question_text');
            $table->string('dimension')->nullable()->after('type');
            $table->string('min_score_description')->nullable()->after('dimension');
            $table->string('max_score_description')->nullable()->after('min_score_description');
            $table->text('peer_question_text')->nullable()->after('max_score_description');
        });

        Schema::create('group_sync_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('score');
            $table->timestamps();

            $table->unique(['survey_id', 'user_id', 'question_id'], 'group_sync_answer_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_sync_answers');

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'dimension',
                'min_score_description',
                'max_score_description',
                'peer_question_text',
            ]);
        });
    }
};
