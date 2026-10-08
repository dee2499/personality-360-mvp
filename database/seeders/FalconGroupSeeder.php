<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\Company;
use App\Models\Question;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class FalconGroupSeeder extends Seeder
{
    /**
     * Run the Falcon Group database seed.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        // 1. Clean existing records
        AssessmentAnswer::truncate();
        DB::table('group_sync_answers')->truncate();
        Assessment::truncate();
        DB::table('survey_participants')->truncate();
        Question::truncate();
        Survey::withTrashed()->forceDelete();

        // Remove non-admin users
        User::where('role', '!=', 'admin')->delete();

        // Remove existing companies
        Company::truncate();

        Schema::enableForeignKeyConstraints();

        // 2. Ensure Admin user: Srinivas Patnaik (srini@saipio.com)
        $srini = User::firstOrCreate(
            ['email' => 'srini@saipio.com'],
            [
                'name' => 'Srinivas Patnaik',
                'password' => Hash::make('Srini@ChangeQuo123'),
                'role' => 'admin',
                'company_id' => null,
                'email_verified_at' => now(),
                'invitation_accepted_at' => now(),
            ]
        );
        $srini->update([
            'password' => Hash::make('Srini@ChangeQuo123'),
            'role' => 'admin',
            'company_id' => null,
        ]);

        // 3. Create Company: Falcon Group
        $company = Company::create([
            'name' => 'Falcon Group',
            'slug' => 'falcon-group-X6geC',
            'contact_email' => 'srini@falcon.com',
            'description' => 'Leadership CQ Score & CQ Sync Score (Change readiness & Adaptability)',
        ]);

        // 4. Create Falcon Manager: Srini (srini@falcon.com, username: srini@falcon)
        User::create([
            'company_id' => $company->id,
            'name' => 'Srini',
            'username' => 'srini@falcon',
            'email' => 'srini@falcon.com',
            'password' => Hash::make('falcon123'),
            'role' => 'manager',
            'email_verified_at' => now(),
            'invitation_accepted_at' => now(),
        ]);

        // 5. Create 27 Falcon Employees / Members
        $employeesData = [
            [
                'name' => 'Ranjan Kumar Behera',
                'username' => 'ranjan@falcon',
                'email' => 'ranjan@falconmarine.co.in',
                'designation' => 'CFO',
                'department' => 'Finance & Accounts',
                'division' => 'Marine',
                'age' => 56,
            ],
            [
                'name' => 'Surajit Satpathy',
                'username' => 'surajit@falcon',
                'email' => 'surajit@falconmarine.co.in',
                'designation' => 'COO',
                'department' => 'Operations',
                'division' => 'Marine & Feed',
                'age' => 56,
            ],
            [
                'name' => 'Anil Prasad Mohanty',
                'username' => 'anil@falcon',
                'email' => 'anilprasadmohanty@falconmarine.co.in',
                'designation' => 'CHRO',
                'department' => 'Human Resources',
                'division' => 'Group',
                'age' => 54,
            ],
            [
                'name' => 'Gauri Shankar Rath',
                'username' => 'gauri@falcon',
                'email' => 'gs.rath@falconfeeds.in',
                'designation' => 'CSMO',
                'department' => 'Sales & Marketing',
                'division' => 'Feed',
                'age' => 58,
            ],
            [
                'name' => 'Sailesh Patnaik',
                'username' => 'sailesh@falcon',
                'email' => 'sailesh@falconmarine.co.in',
                'designation' => 'GM',
                'department' => 'Exports',
                'division' => 'Marine',
                'age' => 56,
            ],
            [
                'name' => 'Anjan Mohanty',
                'username' => 'anjan@falcon',
                'email' => 'anjan@falconmarine.co.in',
                'designation' => 'GM',
                'department' => 'Procurement',
                'division' => 'Marine',
                'age' => 30,
            ],
            [
                'name' => 'Swadesh Sarangi',
                'username' => 'swadesh@falcon',
                'email' => 'swadesh@falconmarine.co.in',
                'designation' => 'GM',
                'department' => 'Retail',
                'division' => 'Fresh',
                'age' => 50,
            ],
            [
                'name' => 'Kamala Kant Dash',
                'username' => 'kamala@falcon',
                'email' => 'works@falconfeeds.in',
                'designation' => 'GM',
                'department' => 'Operations',
                'division' => 'Feed',
                'age' => 53,
            ],
            [
                'name' => 'Gaurav Kaushik',
                'username' => 'gaurav@falcon',
                'email' => 'gauravkaushik@falconrealestate.in',
                'designation' => 'VP',
                'department' => 'Operations',
                'division' => 'Real Estate',
                'age' => 53,
            ],
            [
                'name' => 'Vikrant Gupta',
                'username' => 'vikrant@falcon',
                'email' => 'vikrantgupta@falconrealestate.in',
                'designation' => 'AVP',
                'department' => 'Sales & Marketing',
                'division' => 'Real Estate',
                'age' => 47,
            ],
            [
                'name' => 'Jayanta Ghose',
                'username' => 'jayanta@falcon',
                'email' => 'jayanta.ghose@falconrealestate.in',
                'designation' => 'VP',
                'department' => 'Projects',
                'division' => 'Real Estate',
                'age' => 53,
            ],
            [
                'name' => 'Arun Kumar Mohanty',
                'username' => 'arun@falcon',
                'email' => 'arun.mohanty@falconrealestate.in',
                'designation' => 'DGM',
                'department' => 'Finance & Accounts',
                'division' => 'Real Estate',
                'age' => 34,
            ],
            [
                'name' => 'Sai Prasad Dash',
                'username' => 'sai@falcon',
                'email' => 'saiprasad@falconrealestate.in',
                'designation' => 'GM',
                'department' => 'Legal',
                'division' => 'Real Estate',
                'age' => 42,
            ],
            [
                'name' => 'Tapan Kumar Mohanty',
                'username' => 'tapan@falcon',
                'email' => 'quality@falconfeeds.in',
                'designation' => 'AGM',
                'department' => 'Quality & Formulation',
                'division' => 'Feed',
                'age' => 44,
            ],
            [
                'name' => 'Tupakula Suresh Babu',
                'username' => 'tupakula@falcon',
                'email' => 'tsureshbabu@falconmarine.co.in',
                'designation' => 'GM',
                'department' => 'Operations',
                'division' => 'Marine',
                'age' => 47,
            ],
            [
                'name' => 'Ashutosh Das',
                'username' => 'ashutosh@falcon',
                'email' => 'asutosh@falconmarine.co.in',
                'designation' => 'AGM',
                'department' => 'QA/QC',
                'division' => 'Marine',
                'age' => 46,
            ],
            [
                'name' => 'Snigdha Samir Mohapatra',
                'username' => 'snigdha@falcon',
                'email' => 'purchase@falconfeeds.in',
                'designation' => 'AGM',
                'department' => 'Purchase',
                'division' => 'Feed',
                'age' => 38,
            ],
            [
                'name' => 'Chirag Bansal',
                'username' => 'chirag@falcon',
                'email' => 'chiragb7@gmail.com',
                'designation' => 'AGM',
                'department' => 'Finance & Accounts',
                'division' => 'Feed',
                'age' => 34,
            ],
            [
                'name' => 'Niranjan Mishra',
                'username' => 'niranjan@falcon',
                'email' => 'hr@falconfeeds.in',
                'designation' => 'AGM',
                'department' => 'Human Resources',
                'division' => 'Feed',
                'age' => 53,
            ],
            [
                'name' => 'Biranchi Narayan Biswal',
                'username' => 'biranchi@falcon',
                'email' => 'electrical@falconfeeds.in',
                'designation' => 'AGM',
                'department' => 'Electrical',
                'division' => 'Feed',
                'age' => 49,
            ],
            [
                'name' => 'Umesh Mohapatra',
                'username' => 'umesh@falcon',
                'email' => 'umesh@falconmarine.co.in',
                'designation' => 'AGM',
                'department' => 'Finance & Accounts',
                'division' => 'Marine',
                'age' => 32,
            ],
            [
                'name' => 'Susmita Dutta',
                'username' => 'susmita@falcon',
                'email' => 'susmita@falconmarine.co.in',
                'designation' => 'AGM',
                'department' => 'Company Secretary',
                'division' => 'Group',
                'age' => 37,
            ],
            [
                'name' => 'Sunil Dora',
                'username' => 'sunil@falcon',
                'email' => 'sunil_dora@falconfeeds.in',
                'designation' => 'AGM',
                'department' => 'Sales & Marketing',
                'division' => 'Feed',
                'age' => 42,
            ],
            [
                'name' => 'Syed Zubenoor Ali',
                'username' => 'syed@falcon',
                'email' => 'syed.alli@falconmarine.co.in',
                'designation' => 'AGM',
                'department' => 'Exports',
                'division' => 'Marine',
                'age' => 39,
            ],
            [
                'name' => 'Alok',
                'username' => 'Alok@falcon',
                'email' => 'aloke@falconmarine.co.in',
                'designation' => 'AGM',
                'department' => 'Procurement',
                'division' => 'Marine',
                'age' => 54,
            ],
            [
                'name' => 'Ayushi Verma',
                'username' => 'ayushi@falcon',
                'email' => 'cs@falconholdings.co.in',
                'designation' => 'Company Secretary',
                'department' => 'Secretarial',
                'division' => 'Holdings',
                'age' => 26,
            ],
            [
                'name' => 'Srinivas Patnaik',
                'username' => 'srinivas@falcon',
                'email' => 'srinivas@falcon.com',
                'designation' => null,
                'department' => null,
                'division' => null,
                'age' => null,
            ],
        ];

        $usersByEmail = [];
        foreach ($employeesData as $data) {
            $user = User::create([
                'company_id' => $company->id,
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => Hash::make('falcon123'),
                'role' => 'participant',
                'designation' => $data['designation'] ?? null,
                'department' => $data['department'] ?? null,
                'division' => $data['division'] ?? null,
                'age' => $data['age'] ?? null,
                'email_verified_at' => now(),
                'invitation_accepted_at' => now(),
            ]);
            $usersByEmail[$user->email] = $user;
        }

        // 5. Create Survey: ChangeQuo
        $survey = Survey::create([
            'company_id' => $company->id,
            'title' => 'ChangeQuo',
            'description' => "This assessment measures the change readiness individually and as part of a team.\r\nYou will respond to questions about yourself, others anonymously. Feel free to answer each of them openly and to the best of your knowledge as we are noting putting any report around who said what. the scores will always go as an average input of multiple people. \r\nPlease answer based on your actual experience and observations to provide an accurate reflection of CQ.",
            'status' => 'published',
            'published_at' => now(),
            'created_by' => $srini->id,
        ]);

        // 6. Create 14 Questions (11 Individual CQ + 3 Group Sync)
        $questionsData = [
            [
                'question_text' => 'Do you see life changing around? (Respond to all names given below)',
                'type' => 'individual',
                'dimension' => 'Awareness of Change',
                'min_score_description' => 'No Change',
                'max_score_description' => 'Lot of Change',
                'peer_question_text' => 'Do you see life changing around?',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'question_text' => 'In the recent past, do you know which are top 3 things thats changing for you? (Respond to all names given below)',
                'type' => 'individual',
                'dimension' => 'Understanding Key Changes',
                'min_score_description' => 'No Idea at all',
                'max_score_description' => 'I know it clearly',
                'peer_question_text' => 'In the recent past, do you know which are top 3 things thats changing for this person?',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'question_text' => 'The changes that you have observed in recent past have occurred by situation or by your own choice? (Respond to all names given below)',
                'type' => 'individual',
                'dimension' => 'Choice vs Circumstance',
                'min_score_description' => 'By Situation',
                'max_score_description' => 'By Choice',
                'peer_question_text' => "The changes observed in recent past have occurred by situation or by this person's own choice?",
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'question_text' => 'How much control you think, you have on these changes thats coming your way? (Respond to all names given below)',
                'type' => 'individual',
                'dimension' => 'Control Over Change',
                'min_score_description' => 'No Control',
                'max_score_description' => 'Full Control',
                'peer_question_text' => 'How much control does this person have on the changes coming their way?',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'question_text' => 'Do you know on how to manage the new changes that you are experiencing in recent past? (Respond to all names given below)',
                'type' => 'individual',
                'dimension' => 'Managing Change Knowledge',
                'min_score_description' => 'Dont Know',
                'max_score_description' => 'Yes I know',
                'peer_question_text' => 'Does this person know how to manage the new changes they are experiencing?',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'question_text' => 'What is your confidence level to independently manage the new changes coming your way? (Respond to all names given below)',
                'type' => 'individual',
                'dimension' => 'Confidence in Change',
                'min_score_description' => 'Not Confident',
                'max_score_description' => 'Very Confident',
                'peer_question_text' => "What is this person's confidence level to independently manage new changes?",
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'question_text' => 'Did you speak to your friend, mentor, colleague or family member who can help you adopt the new changes? (Respond to all names given below)',
                'type' => 'individual',
                'dimension' => 'Seeking Support',
                'min_score_description' => 'Not sure whom to reach',
                'max_score_description' => 'I know who to reach & seek help',
                'peer_question_text' => 'Does this person speak to mentors, colleagues, or others who can help them adopt new changes?',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'question_text' => 'Do you have a clear plan by now on what all actions that you need to take and by when? (Respond to all names given below)',
                'type' => 'individual',
                'dimension' => 'Action Planning',
                'min_score_description' => 'Not yet ready',
                'max_score_description' => 'Yes! have a clear plan in hand',
                'peer_question_text' => 'Does this person have a clear plan on what actions they need to take and by when?',
                'sort_order' => 8,
                'is_active' => true,
            ],
            [
                'question_text' => 'Have you started implementing your plan of actions to drive your change? (Respond to all names given below)',
                'type' => 'individual',
                'dimension' => 'Implementing Plan',
                'min_score_description' => 'Still Exploring',
                'max_score_description' => 'started implementing',
                'peer_question_text' => 'Has this person started implementing their plan of actions to drive change?',
                'sort_order' => 9,
                'is_active' => true,
            ],
            [
                'question_text' => 'Do you see any improvements or results coming from your implemented actions as per your plan? (Respond to all names given below)',
                'type' => 'individual',
                'dimension' => 'Seeing Results',
                'min_score_description' => 'Not Yet',
                'max_score_description' => 'Yes I can see some..',
                'peer_question_text' => "Do you see improvements or results coming from this person's implemented actions?",
                'sort_order' => 10,
                'is_active' => true,
            ],
            [
                'question_text' => 'Have you ever tried supporting a friend or family in any of the above mentioned questions in recent past? (Respond to all names given below)',
                'type' => 'individual',
                'dimension' => 'Supporting Others',
                'min_score_description' => 'Concentrating on myself',
                'max_score_description' => 'Yes, extending support',
                'peer_question_text' => 'Has this person supported colleagues or friends through change in the recent past?',
                'sort_order' => 11,
                'is_active' => true,
            ],
            [
                'question_text' => 'Do you believe everyone in this group has a common understanding of the key changes that are happening around us? (Consider as a group and respond)',
                'type' => 'group_sync',
                'dimension' => 'See Together',
                'min_score_description' => 'No Common Understanding',
                'max_score_description' => 'Complete Common Understanding',
                'peer_question_text' => null,
                'sort_order' => 12,
                'is_active' => true,
            ],
            [
                'question_text' => 'Do you believe everyone in this group is aligned on what needs to change and the direction we need to take? (Consider as a group and respond)',
                'type' => 'group_sync',
                'dimension' => 'Agree Together',
                'min_score_description' => 'I dont think so',
                'max_score_description' => 'Yes Completely Aligned',
                'peer_question_text' => null,
                'sort_order' => 13,
                'is_active' => true,
            ],
            [
                'question_text' => 'Do you believe everyone in this group is aligned and committed to taking the actions needed to make the change happen? (Consider as a group and respond)',
                'type' => 'group_sync',
                'dimension' => 'Act Together',
                'min_score_description' => 'Not aligned not committed',
                'max_score_description' => 'Aligned and Committed',
                'peer_question_text' => null,
                'sort_order' => 14,
                'is_active' => true,
            ],
        ];

        $questionsByOrder = [];
        foreach ($questionsData as $qData) {
            $question = Question::create(array_merge($qData, ['survey_id' => $survey->id]));
            $questionsByOrder[$question->sort_order] = $question;
        }

        // 7. Attach all 24 participants to survey
        $survey->participants()->attach(collect($usersByEmail)->pluck('id'));
    }
}
