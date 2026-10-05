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

        // 2. Verify Users count
        $this->assertEquals(26, User::count());
        $this->assertEquals(2, User::where('role', 'admin')->count());
        $this->assertEquals(24, User::where('role', 'participant')->where('company_id', $company->id)->count());

        // Verify Admins
        $this->assertDatabaseHas('users', ['email' => 'admin@example.com', 'role' => 'admin']);
        $this->assertDatabaseHas('users', ['email' => 'srini@saipio.com', 'role' => 'admin']);

        // 3. Verify Survey
        $this->assertEquals(1, Survey::count());
        $survey = Survey::where('title', 'Team Up')->first();
        $this->assertNotNull($survey);
        $this->assertEquals(24, $survey->participants()->count());

        // 4. Verify Questions
        $this->assertEquals(14, Question::where('survey_id', $survey->id)->count());
        $this->assertEquals(11, Question::where('survey_id', $survey->id)->where('type', 'individual')->count());
        $this->assertEquals(3, Question::where('survey_id', $survey->id)->where('type', 'group_sync')->count());

        // 5. Verify Assessments
        $this->assertEquals(576, Assessment::where('survey_id', $survey->id)->count());
        $this->assertEquals(176, Assessment::where('survey_id', $survey->id)->where('status', 'completed')->count());
        $this->assertEquals(400, Assessment::where('survey_id', $survey->id)->where('status', 'pending')->count());

        // 6. Verify Answers
        $this->assertEquals(1936, AssessmentAnswer::count());
        $this->assertEquals(72, DB::table('group_sync_answers')->where('survey_id', $survey->id)->count());

        // 7. Verify Surajit Satpathy scores
        $surajit = User::where('email', 'surajit.satpathy@falcon.com')->first();
        $this->assertNotNull($surajit);
        $surajitSelf = Assessment::where('survey_id', $survey->id)
            ->where('assessor_id', $surajit->id)
            ->where('subject_id', $surajit->id)
            ->first();
        $this->assertEquals('completed', $surajitSelf->status);
        $this->assertEquals(100.0, (float) $surajitSelf->percentage);

        $surajitPeerAvg = Assessment::where('survey_id', $survey->id)
            ->where('assessor_id', '!=', $surajit->id)
            ->where('subject_id', $surajit->id)
            ->where('status', 'completed')
            ->avg('percentage');
        $this->assertEquals(100.0, round($surajitPeerAvg, 1));

        $surajitGroupSync = DB::table('group_sync_answers')
            ->where('survey_id', $survey->id)
            ->where('user_id', $surajit->id)
            ->pluck('score')
            ->toArray();
        $this->assertEquals([10, 10, 10], $surajitGroupSync);

        // 8. Verify Anil Prasad Mohanty scores
        $anil = User::where('email', 'anil.mohanty@falcon.com')->first();
        $this->assertNotNull($anil);
        $anilSelf = Assessment::where('survey_id', $survey->id)
            ->where('assessor_id', $anil->id)
            ->where('subject_id', $anil->id)
            ->first();
        $this->assertEquals('completed', $anilSelf->status);
        $this->assertEquals(80.0, (float) $anilSelf->percentage);

        $anilPeerAvg = Assessment::where('survey_id', $survey->id)
            ->where('assessor_id', '!=', $anil->id)
            ->where('subject_id', $anil->id)
            ->where('status', 'completed')
            ->avg('percentage');
        $this->assertEquals(77.0, round($anilPeerAvg, 1));

        $anilGroupSync = DB::table('group_sync_answers')
            ->where('survey_id', $survey->id)
            ->where('user_id', $anil->id)
            ->pluck('score')
            ->toArray();
        $this->assertEquals([8, 8, 8], $anilGroupSync);

        // 9. Verify Ranjan Kumar Behera scores
        $ranjan = User::where('email', 'ranjan.behera@falcon.com')->first();
        $this->assertNotNull($ranjan);
        $ranjanSelf = Assessment::where('survey_id', $survey->id)
            ->where('assessor_id', $ranjan->id)
            ->where('subject_id', $ranjan->id)
            ->first();
        $this->assertEquals('completed', $ranjanSelf->status);
        $this->assertEquals(100.0, (float) $ranjanSelf->percentage);

        $ranjanPeerAvg = Assessment::where('survey_id', $survey->id)
            ->where('assessor_id', '!=', $ranjan->id)
            ->where('subject_id', $ranjan->id)
            ->where('status', 'completed')
            ->avg('percentage');
        $this->assertEquals(13.9, round($ranjanPeerAvg, 1));

        // 10. Verify Sai Prasad Dash scores
        $sai = User::where('email', 'sai.dash@falcon.com')->first();
        $this->assertNotNull($sai);
        $saiSelf = Assessment::where('survey_id', $survey->id)
            ->where('assessor_id', $sai->id)
            ->where('subject_id', $sai->id)
            ->first();
        $this->assertEquals('completed', $saiSelf->status);
        $this->assertEquals(10.0, (float) $saiSelf->percentage);

        $saiPeerAvg = Assessment::where('survey_id', $survey->id)
            ->where('assessor_id', '!=', $sai->id)
            ->where('subject_id', $sai->id)
            ->where('status', 'completed')
            ->avg('percentage');
        $this->assertEquals(13.9, round($saiPeerAvg, 1));

        $saiGroupSync = DB::table('group_sync_answers')
            ->where('survey_id', $survey->id)
            ->where('user_id', $sai->id)
            ->pluck('score')
            ->toArray();
        $this->assertEquals([1, 1, 1], $saiGroupSync);
    }
}
