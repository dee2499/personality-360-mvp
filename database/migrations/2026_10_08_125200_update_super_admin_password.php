<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $newHash = Hash::make('admin@change123');

        // Update srini@saipio.com and any super admin account
        User::where('email', 'srini@saipio.com')
            ->orWhere('role', 'admin')
            ->update([
                'password' => $newHash,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
