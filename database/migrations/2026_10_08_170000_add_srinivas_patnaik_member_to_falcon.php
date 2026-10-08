<?php

use App\Models\Company;
use App\Models\Survey;
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
        $falconCompany = Company::where('name', 'like', '%Falcon%')->first();

        if ($falconCompany) {
            $user = User::firstOrNew(['email' => 'srinivas@falcon.com']);
            $user->fill([
                'company_id' => $falconCompany->id,
                'name' => 'Srinivas Patnaik',
                'username' => 'srinivas@falcon',
                'role' => 'participant',
                'password' => Hash::make('falcon123'),
                'email_verified_at' => now(),
                'invitation_accepted_at' => now(),
            ]);
            $user->save();

            // Attach to Falcon ChangeQuo survey as a participant
            $falconSurvey = Survey::where('company_id', $falconCompany->id)
                ->where('title', 'ChangeQuo')
                ->first();

            if ($falconSurvey) {
                $falconSurvey->participants()->syncWithoutDetaching([$user->id]);
            }
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
