<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\Company;
use App\Models\Question;
use App\Models\Survey;
use App\Models\User;
use App\Services\AssessmentGenerationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Keeps admin user, cleans all other records, creates 1 company with 20 employees,
     * creates 1 published survey with 11 questions, attaches all 20 employees,
     * and generates the full 360 assessment matrix (400 assessments).
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        // 1. Clean existing records
        AssessmentAnswer::truncate();
        Assessment::truncate();
        DB::table('survey_participants')->truncate();
        Question::truncate();
        Survey::withTrashed()->forceDelete();

        // Remove non-admin users
        User::where('role', '!=', 'admin')->delete();

        // Remove companies
        Company::truncate();

        Schema::enableForeignKeyConstraints();

        // 2. Ensure Admin user exists with password "password"
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'company_id' => null,
            ]
        );
        $admin->update([
            'password' => Hash::make('password'),
            'role' => 'admin',
            'company_id' => null,
        ]);

        // 3. Create 1 Company
        $company = Company::create([
            'name' => 'Acme Corporation',
            'slug' => 'acme-corporation',
            'contact_email' => 'hr@acme.com',
            'description' => 'Leading innovation & organizational culture benchmark.',
        ]);

        // 4. Create 20 Employees for this company with password "password"
        $employeesData = [
            ['name' => 'Alex Morgan', 'email' => 'alex.morgan@acme.com'],
            ['name' => 'Brenda Vance', 'email' => 'brenda.vance@acme.com'],
            ['name' => 'Carlos Diaz', 'email' => 'carlos.diaz@acme.com'],
            ['name' => 'Danielle Brooks', 'email' => 'danielle.brooks@acme.com'],
            ['name' => 'Ethan Wright', 'email' => 'ethan.wright@acme.com'],
            ['name' => 'Fiona Gallagher', 'email' => 'fiona.gallagher@acme.com'],
            ['name' => 'George Clark', 'email' => 'george.clark@acme.com'],
            ['name' => 'Hannah Abbott', 'email' => 'hannah.abbott@acme.com'],
            ['name' => 'Ian Malcolm', 'email' => 'ian.malcolm@acme.com'],
            ['name' => 'Julia Roberts', 'email' => 'julia.roberts@acme.com'],
            ['name' => 'Kevin Bacon', 'email' => 'kevin.bacon@acme.com'],
            ['name' => 'Laura Croft', 'email' => 'laura.croft@acme.com'],
            ['name' => 'Michael Scott', 'email' => 'michael.scott@acme.com'],
            ['name' => 'Natalie Portman', 'email' => 'natalie.portman@acme.com'],
            ['name' => 'Oliver Queen', 'email' => 'oliver.queen@acme.com'],
            ['name' => 'Rachel Green', 'email' => 'rachel.green@acme.com'],
            ['name' => 'Samuel Jackson', 'email' => 'samuel.jackson@acme.com'],
            ['name' => 'Tina Fey', 'email' => 'tina.fey@acme.com'],
            ['name' => 'Victor Stone', 'email' => 'victor.stone@acme.com'],
            ['name' => 'Wendy Rhoades', 'email' => 'wendy.rhoades@acme.com'],
        ];

        $employees = collect();
        foreach ($employeesData as $data) {
            $employee = User::create([
                'company_id' => $company->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => 'participant',
                'invitation_accepted_at' => now(),
            ]);
            $employees->push($employee);
        }

        // 5. Create 1 Survey for Acme Corporation
        $survey = Survey::create([
            'company_id' => $company->id,
            'title' => 'Leadership & Team Performance 360 Survey',
            'description' => 'Comprehensive 360-degree behavioral competency evaluation for Acme Corporation team members.',
            'status' => 'published',
            'published_at' => now(),
            'created_by' => $admin->id,
        ]);

        // 6. Create 11 standard competency questions
        $questions = [
            'How effectively does this person communicate with others?',
            'How well does this person work in a team?',
            'How confidently does this person make decisions?',
            'How adaptable is this person to change?',
            'How well does this person handle responsibility?',
            'How effectively does this person solve problems?',
            'How well does this person listen to others?',
            'How effectively does this person manage conflict?',
            'How consistently does this person demonstrate empathy?',
            'How effectively does this person take initiative?',
            'How reliable is this person when working toward goals?',
        ];

        foreach ($questions as $index => $text) {
            Question::create([
                'survey_id' => $survey->id,
                'question_text' => $text,
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        }

        // 7. Attach all 20 employees to the survey
        $survey->participants()->attach($employees->pluck('id'));

        // 8. Generate all 360 assessment pairings (20 x 20 = 400 assessments)
        app(AssessmentGenerationService::class)->generateForSurvey($survey);
    }
}
