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
        Schema::table('surveys', function (Blueprint $table) {
            $table->string('sign_off_status')->default('pending')->after('status'); // pending, in_review, signed_off
            $table->string('sign_off_lead')->nullable()->after('sign_off_status');
            $table->text('sign_off_notes')->nullable()->after('sign_off_lead');
            $table->timestamp('signed_off_at')->nullable()->after('sign_off_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropColumn([
                'sign_off_status',
                'sign_off_lead',
                'sign_off_notes',
                'signed_off_at',
            ]);
        });
    }
};
