<?php

use App\Models\Company;
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
        $falcon = Company::where('name', 'like', '%Falcon%')->first();

        $manager = User::where('email', 'srini@falcon.com')->first();
        if (! $manager && $falcon) {
            $manager = User::firstOrNew(['company_id' => $falcon->id, 'role' => 'manager']);
        }

        if ($manager) {
            $manager->update([
                'name' => 'Srini',
                'username' => 'srini@falcon',
                'email' => 'srini@falcon.com',
                'role' => 'manager',
                'password' => Hash::make('falcon123'),
                'company_id' => $falcon?->id ?? $manager->company_id,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
