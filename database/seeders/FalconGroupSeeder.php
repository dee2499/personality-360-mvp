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

        // 4. Create Falcon Manager: Srini (srini@falcon.com)
        User::create([
            'company_id' => $company->id,
            'name' => 'Srini',
            'email' => 'srini@falcon.com',
            'password' => Hash::make('falcon123'),
            'role' => 'manager',
            'email_verified_at' => now(),
            'invitation_accepted_at' => now(),
        ]);

        // 5. Create 24 Falcon Employees
        $employeesData = [
            ['name' => 'Ranjan Kumar Behera', 'email' => 'ranjan.behera@falcon.com'],
            ['name' => 'Surajit Satpathy', 'email' => 'surajit.satpathy@falcon.com'],
            ['name' => 'Anil Prasad Mohanty', 'email' => 'anil.mohanty@falcon.com'],
            ['name' => 'Gauri Shankar Rath', 'email' => 'gauri.rath@falcon.com'],
            ['name' => 'Sailesh Patnaik', 'email' => 'sailesh.patnaik@falcon.com'],
            ['name' => 'Anjan Mohanty', 'email' => 'anjan.mohanty@falcon.com'],
            ['name' => 'Swadesh Sarangi', 'email' => 'swadesh.sarangi@falcon.com'],
            ['name' => 'Kamala Kant Dash', 'email' => 'kamala.dash@falcon.com'],
            ['name' => 'Gaurav Kaushik', 'email' => 'gaurav.kaushik@falcon.com'],
            ['name' => 'Vikrant Gupta', 'email' => 'vikrant.gupta@falcon.com'],
            ['name' => 'Jayanta Ghose', 'email' => 'jayanta.ghose@falcon.com'],
            ['name' => 'Arun Kumar Mohanty', 'email' => 'arun.mohanty@falcon.com'],
            ['name' => 'Sai Prasad Dash', 'email' => 'sai.dash@falcon.com'],
            ['name' => 'Tapan Kumar Mohanty', 'email' => 'tapan.mohanty@falcon.com'],
            ['name' => 'Tupakula Suresh Babu', 'email' => 'tupakula.babu@falcon.com'],
            ['name' => 'Ashutosh Das', 'email' => 'ashutosh.das@falcon.com'],
            ['name' => 'Snigdha Samir Mohapatra', 'email' => 'snigdha.mohapatra@falcon.com'],
            ['name' => 'Chirag Bansal', 'email' => 'chirag.bansal@falcon.com'],
            ['name' => 'Niranjan Mishra', 'email' => 'niranjan.mishra@falcon.com'],
            ['name' => 'Biranchi Narayan Biswal', 'email' => 'biranchi.biswal@falcon.com'],
            ['name' => 'Umesh Mohapatra', 'email' => 'umesh.mohapatra@falcon.com'],
            ['name' => 'Susmita Dutta', 'email' => 'susmita.dutta@falcon.com'],
            ['name' => 'Sunil Dora', 'email' => 'sunil.dora@falcon.com'],
            ['name' => 'Syed Zubenoor Ali', 'email' => 'syed.ali@falcon.com'],
        ];

        $usersByEmail = [];
        foreach ($employeesData as $data) {
            $user = User::create([
                'company_id' => $company->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('falcon123'),
                'role' => 'participant',
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
                'question_text' => 'Do you see life changing around - This Person?*',
                'type' => 'individual',
                'dimension' => 'Awareness of Change',
                'min_score_description' => 'No Change',
                'max_score_description' => 'Lot of Change',
                'peer_question_text' => 'Do you see life changing around - This Person?',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'question_text' => 'In the recent past, do you know which are top 3 things thats changing for you?*',
                'type' => 'individual',
                'dimension' => 'Understanding Key Changes',
                'min_score_description' => 'No Idea at all',
                'max_score_description' => 'I know it clearly',
                'peer_question_text' => 'In the recent past, do you know which are top 3 things thats changing for this person?',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'question_text' => 'The changes that you have observed in recent past have occurred by situation or by your own choice?*',
                'type' => 'individual',
                'dimension' => 'Choice vs Circumstance',
                'min_score_description' => 'By Situation',
                'max_score_description' => 'By Choice',
                'peer_question_text' => "The changes observed in recent past have occurred by situation or by this person's own choice?",
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'question_text' => 'How much control you think, you have on these changes thats coming your way?*',
                'type' => 'individual',
                'dimension' => 'Control Over Change',
                'min_score_description' => 'No Control',
                'max_score_description' => 'Full Control',
                'peer_question_text' => 'How much control does this person have on the changes coming their way?',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'question_text' => 'Do you know on how to manage the new changes that you are experiencing in recent past?*',
                'type' => 'individual',
                'dimension' => 'Managing Change Knowledge',
                'min_score_description' => 'Dont Know',
                'max_score_description' => 'Yes I know',
                'peer_question_text' => 'Does this person know how to manage the new changes they are experiencing?',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'question_text' => 'What is your confidence level to independently manage the new changes coming your way?*',
                'type' => 'individual',
                'dimension' => 'Confidence in Change',
                'min_score_description' => 'Not Confident',
                'max_score_description' => 'Very Confident',
                'peer_question_text' => "What is this person's confidence level to independently manage new changes?",
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'question_text' => 'Did you speak to your friend, mentor, colleague or family member who can help you adopt the new changes?*',
                'type' => 'individual',
                'dimension' => 'Seeking Support',
                'min_score_description' => 'Not sure whom to reach',
                'max_score_description' => 'I know who to reach & seek help',
                'peer_question_text' => 'Does this person speak to mentors, colleagues, or others who can help them adopt new changes?',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'question_text' => 'Do you have a clear plan by now on what all actions that you need to take and by when?*',
                'type' => 'individual',
                'dimension' => 'Action Planning',
                'min_score_description' => 'Not yet ready',
                'max_score_description' => 'Yes! have a clear plan in hand',
                'peer_question_text' => 'Does this person have a clear plan on what actions they need to take and by when?',
                'sort_order' => 8,
                'is_active' => true,
            ],
            [
                'question_text' => 'Have you started implementing your plan of actions to drive your change?*',
                'type' => 'individual',
                'dimension' => 'Implementing Plan',
                'min_score_description' => 'Still Exploring',
                'max_score_description' => 'started implementing',
                'peer_question_text' => 'Has this person started implementing their plan of actions to drive change?',
                'sort_order' => 9,
                'is_active' => true,
            ],
            [
                'question_text' => 'Do you see any improvements or results coming from your implemented actions as per your plan?*',
                'type' => 'individual',
                'dimension' => 'Seeing Results',
                'min_score_description' => 'Not Yet',
                'max_score_description' => 'Yes I can see some..',
                'peer_question_text' => "Do you see improvements or results coming from this person's implemented actions?",
                'sort_order' => 10,
                'is_active' => true,
            ],
            [
                'question_text' => 'Have you ever tried supporting a friend or family in any of the above mentioned questions in recent past?*',
                'type' => 'individual',
                'dimension' => 'Supporting Others',
                'min_score_description' => 'Concentrating on myself',
                'max_score_description' => 'Yes, extending support',
                'peer_question_text' => 'Has this person supported colleagues or friends through change in the recent past?',
                'sort_order' => 11,
                'is_active' => true,
            ],
            [
                'question_text' => 'Do you believe everyone in this group has a common understanding of the key changes that are happening around us?',
                'type' => 'group_sync',
                'dimension' => 'See Together',
                'min_score_description' => 'No Common Understanding',
                'max_score_description' => 'Complete Common Understanding',
                'peer_question_text' => null,
                'sort_order' => 12,
                'is_active' => true,
            ],
            [
                'question_text' => 'Do you believe everyone in this group is aligned on what needs to change and the direction we need to take?',
                'type' => 'group_sync',
                'dimension' => 'Agree Together',
                'min_score_description' => 'I dont think so',
                'max_score_description' => 'Yes Completely Aligned',
                'peer_question_text' => null,
                'sort_order' => 13,
                'is_active' => true,
            ],
            [
                'question_text' => 'Do you believe everyone in this group is aligned and committed to taking the actions needed to make the change happen?',
                'type' => 'group_sync',
                'dimension' => 'Act Together',
                'min_score_description' => 'not aligned not committed',
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
