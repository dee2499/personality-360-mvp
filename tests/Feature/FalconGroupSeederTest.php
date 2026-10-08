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
        // Run DatabaseSeeder (which calls ScoreCategorySeeder, FalconGroupSeeder, and GcodeSeeder)
        $this->seed(DatabaseSeeder::class);

        // 1. Verify Companies (Falcon Group and GCODE)
        $this->assertEquals(2, Company::count());
        $this->assertDatabaseHas('companies', [
            'name' => 'Falcon Group',
            'contact_email' => 'srini@falcon.com',
        ]);
        $this->assertDatabaseHas('companies', [
            'name' => 'GCODE',
            'contact_email' => 'srini@gcode.in',
        ]);

        $falcon = Company::where('name', 'Falcon Group')->first();
        $gcode = Company::where('name', 'GCODE')->first();
        $this->assertNotNull($falcon);
        $this->assertNotNull($gcode);

        // 2. Verify Users: 1 Admin, 2 Managers (1 Falcon, 1 GCODE), 31 Participants (27 Falcon, 4 GCODE) = 34 Total Users
        $this->assertEquals(34, User::count());
        $this->assertEquals(1, User::where('role', 'admin')->count());
        $this->assertEquals(2, User::where('role', 'manager')->count());
        $this->assertEquals(31, User::where('role', 'participant')->count());

        // Verify Master Admin
        $this->assertDatabaseHas('users', ['email' => 'srini@saipio.com', 'role' => 'admin', 'company_id' => null]);

        // Verify Managers
        $this->assertDatabaseHas('users', ['email' => 'srini@falcon.com', 'role' => 'manager', 'company_id' => $falcon->id]);
        $this->assertDatabaseHas('users', ['email' => 'srini@gcode.in', 'role' => 'manager', 'company_id' => $gcode->id]);

        // Verify Falcon Employees (27 members including Alok, Ayushi, Srinivas)
        $this->assertEquals(27, User::where('role', 'participant')->where('company_id', $falcon->id)->count());
        $this->assertDatabaseHas('users', ['username' => 'srinivas@falcon', 'email' => 'srinivas@falcon.com', 'company_id' => $falcon->id]);

        // Verify GCODE Employees
        $this->assertEquals(4, User::where('role', 'participant')->where('company_id', $gcode->id)->count());
        $this->assertDatabaseHas('users', ['email' => 'shashwat@gcode.in', 'company_id' => $gcode->id]);
        $this->assertDatabaseHas('users', ['email' => 'atharva@gcode.in', 'company_id' => $gcode->id]);
        $this->assertDatabaseHas('users', ['email' => 'lavya@gcode.in', 'company_id' => $gcode->id]);
        $this->assertDatabaseHas('users', ['email' => 'connect@gcode.in', 'company_id' => $gcode->id]);

        // 3. Verify Surveys (ChangeQuo for Falcon Group and ChangeQuo for GCODE)
        $this->assertEquals(2, Survey::count());
        $falconSurvey = Survey::where('company_id', $falcon->id)->where('title', 'ChangeQuo')->first();
        $gcodeSurvey = Survey::where('company_id', $gcode->id)->where('title', 'ChangeQuo')->first();

        $this->assertNotNull($falconSurvey);
        $this->assertNotNull($gcodeSurvey);

        $this->assertEquals(27, $falconSurvey->participants()->count());
        $this->assertEquals(4, $gcodeSurvey->participants()->count());

        // 4. Verify Questions (14 each = 28 total: 11 individual + 3 group_sync each)
        $this->assertEquals(14, Question::where('survey_id', $falconSurvey->id)->count());
        $this->assertEquals(11, Question::where('survey_id', $falconSurvey->id)->where('type', 'individual')->count());
        $this->assertEquals(3, Question::where('survey_id', $falconSurvey->id)->where('type', 'group_sync')->count());

        $this->assertEquals(14, Question::where('survey_id', $gcodeSurvey->id)->count());
        $this->assertEquals(11, Question::where('survey_id', $gcodeSurvey->id)->where('type', 'individual')->count());
        $this->assertEquals(3, Question::where('survey_id', $gcodeSurvey->id)->where('type', 'group_sync')->count());

        // 5. Verify NO Assessments, NO Pairings, NO Answers (will be created by users later)
        $this->assertEquals(0, Assessment::count());
        $this->assertEquals(0, AssessmentAnswer::count());
        $this->assertEquals(0, DB::table('group_sync_answers')->count());
    }
}
