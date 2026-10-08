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
        // 1. Update the 24 Falcon leadership employees with official emails and profile metadata
        $falconProfiles = [
            'Ranjan Kumar Behera' => [
                'email' => 'ranjan@falconmarine.co.in',
                'username' => 'ranjan@falcon',
                'designation' => 'CFO',
                'department' => 'Finance & Accounts',
                'division' => 'Marine',
                'age' => 56,
            ],
            'Surajit Satpathy' => [
                'email' => 'surajit@falconmarine.co.in',
                'username' => 'surajit@falcon',
                'designation' => 'COO',
                'department' => 'Operations',
                'division' => 'Marine & Feed',
                'age' => 56,
            ],
            'Anil Prasad Mohanty' => [
                'email' => 'anilprasadmohanty@falconmarine.co.in',
                'username' => 'anil@falcon',
                'designation' => 'CHRO',
                'department' => 'Human Resources',
                'division' => 'Group',
                'age' => 54,
            ],
            'Gauri Shankar Rath' => [
                'email' => 'gs.rath@falconfeeds.in',
                'username' => 'gauri@falcon',
                'designation' => 'CSMO',
                'department' => 'Sales & Marketing',
                'division' => 'Feed',
                'age' => 58,
            ],
            'Sailesh Patnaik' => [
                'email' => 'sailesh@falconmarine.co.in',
                'username' => 'sailesh@falcon',
                'designation' => 'GM',
                'department' => 'Exports',
                'division' => 'Marine',
                'age' => 56,
            ],
            'Anjan Mohanty' => [
                'email' => 'anjan@falconmarine.co.in',
                'username' => 'anjan@falcon',
                'designation' => 'GM',
                'department' => 'Procurement',
                'division' => 'Marine',
                'age' => 30,
            ],
            'Swadesh Sarangi' => [
                'email' => 'swadesh@falconmarine.co.in',
                'username' => 'swadesh@falcon',
                'designation' => 'GM',
                'department' => 'Retail',
                'division' => 'Fresh',
                'age' => 50,
            ],
            'Kamala Kant Dash' => [
                'email' => 'works@falconfeeds.in',
                'username' => 'kamala@falcon',
                'designation' => 'GM',
                'department' => 'Operations',
                'division' => 'Feed',
                'age' => 53,
            ],
            'Gaurav Kaushik' => [
                'email' => 'gauravkaushik@falconrealestate.in',
                'username' => 'gaurav@falcon',
                'designation' => 'VP',
                'department' => 'Operations',
                'division' => 'Real Estate',
                'age' => 53,
            ],
            'Vikrant Gupta' => [
                'email' => 'vikrantgupta@falconrealestate.in',
                'username' => 'vikrant@falcon',
                'designation' => 'AVP',
                'department' => 'Sales & Marketing',
                'division' => 'Real Estate',
                'age' => 47,
            ],
            'Jayanta Ghose' => [
                'email' => 'jayanta.ghose@falconrealestate.in',
                'username' => 'jayanta@falcon',
                'designation' => 'VP',
                'department' => 'Projects',
                'division' => 'Real Estate',
                'age' => 53,
            ],
            'Arun Kumar Mohanty' => [
                'email' => 'arun.mohanty@falconrealestate.in',
                'username' => 'arun@falcon',
                'designation' => 'DGM',
                'department' => 'Finance & Accounts',
                'division' => 'Real Estate',
                'age' => 34,
            ],
            'Sai Prasad Dash' => [
                'email' => 'saiprasad@falconrealestate.in',
                'username' => 'sai@falcon',
                'designation' => 'GM',
                'department' => 'Legal',
                'division' => 'Real Estate',
                'age' => 42,
            ],
            'Tapan Kumar Mohanty' => [
                'email' => 'quality@falconfeeds.in',
                'username' => 'tapan@falcon',
                'designation' => 'AGM',
                'department' => 'Quality & Formulation',
                'division' => 'Feed',
                'age' => 44,
            ],
            'Tupakula Suresh Babu' => [
                'email' => 'tsureshbabu@falconmarine.co.in',
                'username' => 'tupakula@falcon',
                'designation' => 'GM',
                'department' => 'Operations',
                'division' => 'Marine',
                'age' => 47,
            ],
            'Ashutosh Das' => [
                'email' => 'asutosh@falconmarine.co.in',
                'username' => 'ashutosh@falcon',
                'designation' => 'AGM',
                'department' => 'QA/QC',
                'division' => 'Marine',
                'age' => 46,
            ],
            'Snigdha Samir Mohapatra' => [
                'email' => 'purchase@falconfeeds.in',
                'username' => 'snigdha@falcon',
                'designation' => 'AGM',
                'department' => 'Purchase',
                'division' => 'Feed',
                'age' => 38,
            ],
            'Chirag Bansal' => [
                'email' => 'chiragb7@gmail.com',
                'username' => 'chirag@falcon',
                'designation' => 'AGM',
                'department' => 'Finance & Accounts',
                'division' => 'Feed',
                'age' => 34,
            ],
            'Niranjan Mishra' => [
                'email' => 'hr@falconfeeds.in',
                'username' => 'niranjan@falcon',
                'designation' => 'AGM',
                'department' => 'Human Resources',
                'division' => 'Feed',
                'age' => 53,
            ],
            'Biranchi Narayan Biswal' => [
                'email' => 'electrical@falconfeeds.in',
                'username' => 'biranchi@falcon',
                'designation' => 'AGM',
                'department' => 'Electrical',
                'division' => 'Feed',
                'age' => 49,
            ],
            'Umesh Mohapatra' => [
                'email' => 'umesh@falconmarine.co.in',
                'username' => 'umesh@falcon',
                'designation' => 'AGM',
                'department' => 'Finance & Accounts',
                'division' => 'Marine',
                'age' => 32,
            ],
            'Susmita Dutta' => [
                'email' => 'susmita@falconmarine.co.in',
                'username' => 'susmita@falcon',
                'designation' => 'AGM',
                'department' => 'Company Secretary',
                'division' => 'Group',
                'age' => 37,
            ],
            'Sunil Dora' => [
                'email' => 'sunil_dora@falconfeeds.in',
                'username' => 'sunil@falcon',
                'designation' => 'AGM',
                'department' => 'Sales & Marketing',
                'division' => 'Feed',
                'age' => 42,
            ],
            'Syed Zubenoor Ali' => [
                'email' => 'syed.alli@falconmarine.co.in',
                'username' => 'syed@falcon',
                'designation' => 'AGM',
                'department' => 'Exports',
                'division' => 'Marine',
                'age' => 39,
            ],
        ];

        foreach ($falconProfiles as $name => $data) {
            $user = User::where('name', $name)->first();
            if ($user) {
                $user->update([
                    'email' => $data['email'],
                    'username' => $data['username'],
                    'designation' => $data['designation'],
                    'department' => $data['department'],
                    'division' => $data['division'],
                    'age' => $data['age'],
                    'password' => Hash::make('falcon123'),
                ]);
            }
        }

        // 2. Add or update Mr. Alok Panda
        $falconCompany = Company::where('name', 'like', '%Falcon%')->first();
        if ($falconCompany) {
            $alok = User::firstOrNew(['email' => 'aloke@falconmarine.co.in']);
            $alok->fill([
                'company_id' => $falconCompany->id,
                'name' => 'Alok',
                'username' => 'Alok@falcon',
                'role' => 'participant',
                'designation' => 'AGM',
                'department' => 'Procurement',
                'division' => 'Marine',
                'age' => 54,
                'password' => Hash::make('falcon123'),
                'email_verified_at' => now(),
                'invitation_accepted_at' => now(),
            ]);
            $alok->save();

            // Attach to Falcon ChangeQuo survey as a participant
            $falconSurvey = Survey::where('company_id', $falconCompany->id)
                ->where('title', 'ChangeQuo')
                ->first();

            if ($falconSurvey) {
                $falconSurvey->participants()->syncWithoutDetaching([$alok->id]);
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
