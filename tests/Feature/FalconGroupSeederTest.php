<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\Company;
use App\Models\Question;
use App\Models\Survey;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FalconGroupSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_populates_exact_falcon_group_dataset(): void
    {
        // Run DatabaseSeeder (which calls ScoreCategorySeeder & FalconGroupSeeder)
        $this->seed(DatabaseSeeder::class);

        // 1. Verify Company
        $this->assertDatabaseHas('companies', [
            'name' => 'Falcon Group',
            'contact_email' => 'srini@saipio.com',
        ]);
        $company = Company::where('name', 'Falcon Group')->first();
        $this->assertNotNull($company);

        // 2. Verify Users count (1 admin: srini only, 24 participants)
        $this->assertEquals(25, User::count());
        $this->assertEquals(1, User::where('role', 'admin')->count());
        $this->assertEquals(24, User::where('role', 'participant')->where('company_id', $company->id)->count());

        // Verify Admins
        $this->assertDatabaseMissing('users', ['email' => 'admin@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'srini@saipio.com', 'role' => 'admin']);

        // 3. Verify Survey
        $this->assertEquals(1, Survey::count());
        $survey = Survey::where('title', 'Team Up')->first();
        $srini = User::where('email', 'srini@saipio.com')->first();
        $this->assertNotNull($srini);
        $this->assertEquals($srini->id, $survey->created_by);
        $this->assertEquals(24, $survey->participants()->count());

        // 4. Verify Questions
        $this->assertEquals(14, Question::where('survey_id', $survey->id)->count());
        $this->assertEquals(11, Question::where('survey_id', $survey->id)->where('type', 'individual')->count());
        $this->assertEquals(3, Question::where('survey_id', $survey->id)->where('type', 'group_sync')->count());

        // 5. Verify No Assessments, No Pairings, No Answers
        $this->assertEquals(0, Assessment::count());
        $this->assertEquals(0, AssessmentAnswer::count());
        $this->assertEquals(0, DB::table('group_sync_answers')->count());
    }
}
