<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\Company;
use App\Models\Question;
use App\Models\Survey;
use App\Models\User;
use App\Services\AssessmentCategoryService;
use App\Services\AssessmentScoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChangeQuotientReportAndGroupInsightsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        AssessmentCategoryService::clearCache();
    }

    protected function setupSurveyWithParticipants(): array
    {
        $company = Company::factory()->create();

        $admin = User::factory()->create([
            'role' => 'admin',
            'company_id' => $company->id,
        ]);

        $alice = User::factory()->create([
            'name' => 'Alice Confidential Subject',
            'role' => 'participant',
            'company_id' => $company->id,
        ]);

        $bob = User::factory()->create([
            'name' => 'Bob Peer Assessor',
            'role' => 'participant',
            'company_id' => $company->id,
        ]);

        $charlie = User::factory()->create([
            'name' => 'Charlie Peer Assessor',
            'role' => 'participant',
            'company_id' => $company->id,
        ]);

        $survey = Survey::create([
            'company_id' => $company->id,
            'title' => 'Leadership Agility & CQ Assessment',
            'description' => '360 degree change agility survey',
            'status' => 'published',
            'published_at' => now(),
            'created_by' => $admin->id,
        ]);

        $survey->participants()->attach([$alice->id, $bob->id, $charlie->id]);

        $q1 = Question::create([
            'survey_id' => $survey->id,
            'question_text' => 'How adaptable is this person to change and organizational shifts?',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $q2 = Question::create([
            'survey_id' => $survey->id,
            'question_text' => 'How consistently does this person demonstrate empathy and support colleagues?',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $q3 = Question::create([
            'survey_id' => $survey->id,
            'question_text' => 'How effectively does this person manage conflict and facilitate alignment?',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        // Alice Self-Assessment (assessor = Alice, subject = Alice)
        $selfAssessment = Assessment::create([
            'survey_id' => $survey->id,
            'assessor_id' => $alice->id,
            'subject_id' => $alice->id,
            'status' => 'completed',
            'started_at' => now(),
            'completed_at' => now(),
            'total_score' => 27, // 9 + 9 + 9
            'max_score' => 30,
            'percentage' => 90.0,
            'category' => 'Catalyst',
        ]);
        AssessmentAnswer::create(['assessment_id' => $selfAssessment->id, 'question_id' => $q1->id, 'score' => 9]);
        AssessmentAnswer::create(['assessment_id' => $selfAssessment->id, 'question_id' => $q2->id, 'score' => 9]);
        AssessmentAnswer::create(['assessment_id' => $selfAssessment->id, 'question_id' => $q3->id, 'score' => 9]);

        // Bob evaluating Alice (assessor = Bob, subject = Alice)
        $bobAssessment = Assessment::create([
            'survey_id' => $survey->id,
            'assessor_id' => $bob->id,
            'subject_id' => $alice->id,
            'status' => 'completed',
            'started_at' => now(),
            'completed_at' => now(),
            'total_score' => 24, // 8 + 8 + 8
            'max_score' => 30,
            'percentage' => 80.0,
            'category' => 'Strategist',
        ]);
        AssessmentAnswer::create(['assessment_id' => $bobAssessment->id, 'question_id' => $q1->id, 'score' => 8]);
        AssessmentAnswer::create(['assessment_id' => $bobAssessment->id, 'question_id' => $q2->id, 'score' => 8]);
        AssessmentAnswer::create(['assessment_id' => $bobAssessment->id, 'question_id' => $q3->id, 'score' => 8]);

        // Charlie evaluating Alice (assessor = Charlie, subject = Alice)
        $charlieAssessment = Assessment::create([
            'survey_id' => $survey->id,
            'assessor_id' => $charlie->id,
            'subject_id' => $alice->id,
            'status' => 'completed',
            'started_at' => now(),
            'completed_at' => now(),
            'total_score' => 24, // 8 + 8 + 8
            'max_score' => 30,
            'percentage' => 80.0,
            'category' => 'Strategist',
        ]);
        AssessmentAnswer::create(['assessment_id' => $charlieAssessment->id, 'question_id' => $q1->id, 'score' => 8]);
        AssessmentAnswer::create(['assessment_id' => $charlieAssessment->id, 'question_id' => $q2->id, 'score' => 8]);
        AssessmentAnswer::create(['assessment_id' => $charlieAssessment->id, 'question_id' => $q3->id, 'score' => 8]);

        return compact('company', 'admin', 'alice', 'bob', 'charlie', 'survey', 'q1', 'q2', 'q3');
    }

    public function test_participant_can_view_confidential_individual_cq_report(): void
    {
        $data = $this->setupSurveyWithParticipants();

        $response = $this->actingAs($data['alice'])->get(route('participant.assessments.report', $data['survey']));
        $response->assertOk();

        // 1. Check Confidentiality Guarantees & Headers
        $response->assertSee('Confidential');
        $response->assertSee('Alice Confidential Subject');
        $response->assertSee('ChangeQuo');

        // 2. Requirement 1: Self Score
        $response->assertSee('Your Self Score');
        $response->assertSee('9.0'); // Alice self rating average

        // 3. Requirement 2: Peer Score (Average)
        $response->assertSee('Peer Score (Average)');
        $response->assertSee('8.0'); // Observer consensus

        // 4. Requirement 3: Moderated Score & Archetype
        // Moderated = (9.0 + 8.0) / 2 = 8.5 (Achiever / Change Champion)
        $response->assertSee('8.5');
        $response->assertSee('Achiever');
        $response->assertSee('Change Champion');

        // 5. 2x2 Self vs Peer Insight Matrix & Key Insights
        $response->assertSee('Self vs Peer Insight');
        $response->assertSee('Aligned Strength');
        $response->assertSee('Key Insights');
        $response->assertSee('Your Strengths');

        // 6. Question-by-question matrix
        $response->assertSee('11 Change Journey Dimensions');

        // Verify peer assessor names are NOT exposed on Alice's confidential report
        $response->assertDontSee('Bob Peer Assessor');
        $response->assertDontSee('Charlie Peer Assessor');
    }

    public function test_unauthorized_user_cannot_access_survey_report_they_do_not_belong_to(): void
    {
        $data = $this->setupSurveyWithParticipants();

        $outsider = User::factory()->create([
            'role' => 'participant',
        ]);

        $response = $this->actingAs($outsider)->get(route('participant.assessments.report', $data['survey']));
        $response->assertForbidden();
    }

    public function test_participant_can_view_anonymous_team_group_insights(): void
    {
        $data = $this->setupSurveyWithParticipants();

        $response = $this->actingAs($data['alice'])->get(route('participant.surveys.group-insights', $data['survey']));
        $response->assertOk();

        // Check group insights page content
        $response->assertSee('Team ChangeQuo Report');
        $response->assertSee('Zero Names Exposed');
        $response->assertSee('Team CQ (Group Score)');
        $response->assertSee('Team CQ Sync');
        $response->assertSee('Team Position Matrix');

        // Peer assessor and subject names are NOT displayed in the anonymous team insights
        $response->assertDontSee('Bob Peer Assessor');
        $response->assertDontSee('Charlie Peer Assessor');
    }

    public function test_admin_can_view_group_insights_and_sign_off(): void
    {
        $data = $this->setupSurveyWithParticipants();

        $response = $this->actingAs($data['admin'])->get(route('admin.surveys.group-insights', $data['survey']));
        $response->assertOk();

        // ZERO individual participant names should appear anywhere in admin group insights
        $response->assertDontSee('Alice Confidential Subject');
        $response->assertDontSee('Bob Peer Assessor');
        $response->assertDontSee('Charlie Peer Assessor');

        $response->assertSee('Team ChangeQuo Report');
        $response->assertSee('Confidential • Zero Names Exposed');
        $response->assertSee('Leadership Sign-Off');
        $response->assertSee('Executive Sponsor / Leadership Lead');

        // Admin submits sign-off
        $signOffResponse = $this->actingAs($data['admin'])->post(route('admin.surveys.sign-off', $data['survey']), [
            'sign_off_lead' => 'Chief People Officer Sarah Vance',
            'sign_off_status' => 'approved',
            'sign_off_notes' => 'Agreed on bi-weekly change agility retrospectives and cross-functional mentoring.',
        ]);

        $signOffResponse->assertRedirect(route('admin.surveys.group-insights', $data['survey']));
        $signOffResponse->assertSessionHas('success');

        // Verify database updated
        $data['survey']->refresh();
        $this->assertSame('approved', $data['survey']->sign_off_status);
        $this->assertSame('Chief People Officer Sarah Vance', $data['survey']->sign_off_lead);
        $this->assertSame('Agreed on bi-weekly change agility retrospectives and cross-functional mentoring.', $data['survey']->sign_off_notes);
        $this->assertNotNull($data['survey']->signed_off_at);

        // Verify sign-off is reflected on the page
        $followUp = $this->actingAs($data['admin'])->get(route('admin.surveys.group-insights', $data['survey']));
        $followUp->assertOk();
        $followUp->assertSee('Chief People Officer Sarah Vance');
        $followUp->assertSee('Status: Approved');
    }

    public function test_sign_off_validation_requires_lead_and_valid_status(): void
    {
        $data = $this->setupSurveyWithParticipants();

        $response = $this->actingAs($data['admin'])->post(route('admin.surveys.sign-off', $data['survey']), [
            'sign_off_lead' => '',
            'sign_off_status' => 'invalid_status',
        ]);

        $response->assertSessionHasErrors(['sign_off_lead', 'sign_off_status']);
    }

    public function test_participant_assessment_index_has_confidential_cq_report_link(): void
    {
        $data = $this->setupSurveyWithParticipants();

        $response = $this->actingAs($data['alice'])->get(route('participant.assessments.index', ['survey_id' => $data['survey']->id]));
        $response->assertOk();

        $response->assertSee(route('participant.assessments.report', $data['survey']));
        $response->assertSee('Confidential CQ Report');
        $response->assertSee(route('participant.surveys.group-insights', $data['survey']));
        $response->assertSee('Team Insights');
    }

    public function test_admin_survey_show_has_group_insights_button(): void
    {
        $data = $this->setupSurveyWithParticipants();

        $response = $this->actingAs($data['admin'])->get(route('admin.surveys.show', $data['survey']));
        $response->assertOk();

        $response->assertSee(route('admin.surveys.group-insights', $data['survey']));
        $response->assertSee('Group Insights & Sign-Off', false);
    }

    public function test_participant_can_access_cq_report_via_user_tab_route(): void
    {
        $data = $this->setupSurveyWithParticipants();

        $response = $this->actingAs($data['alice'])->get(route('participant.cq-report'));
        $response->assertOk();

        // 1. Navigation sidebar and brand
        $response->assertSee('CQ Report');
        $response->assertSee('My Assessments');
        $response->assertSee('changequo', false);
        $response->assertSee('INDIVIDUAL CQ REPORT');

        // 2. Profile Speedometer & Categories
        $response->assertSee('Your ChangeQuo Profile');
        $response->assertSee('Score is on a scale of 1 – 10');
        $response->assertSee('Resistant');
        $response->assertSee('Follower');
        $response->assertSee('Supporter');
        $response->assertSee('Driver');
        $response->assertSee('Champion');

        // 3. Two confidential score cards
        $response->assertSee('Your Self Score');
        $response->assertSee('Peer Score (Average)');
        $response->assertSee('Confidential');

        // 4. Self vs Peer Insight 2x2 Matrix & Quadrants
        $response->assertSee('Self vs Peer Insight');
        $response->assertSee('Undervalued Potential');
        $response->assertSee('Aligned Strength');
        $response->assertSee('Key Development Area');
        $response->assertSee('Perception Gap');

        // 5. Key Insights & Top 3 Recommended Actions
        $response->assertSee('Key Insights');
        $response->assertSee('Your Strengths');
        $response->assertSee('Development Areas');
        $response->assertSee('Your Top 3 Recommended Actions');
        $response->assertSee('Increase Visibility and Communication');
        $response->assertSee('Seek and Leverage Support');
        $response->assertSee('Enable and Develop Others');
    }

    public function test_cq_report_renders_driver_profile_and_perception_gap_for_self_8_1_and_peer_6_4(): void
    {
        $company = Company::factory()->create();
        $admin = User::factory()->create(['role' => 'admin', 'company_id' => $company->id]);
        $alex = User::factory()->create(['name' => 'Alex Driver', 'role' => 'participant', 'company_id' => $company->id]);
        $peer1 = User::factory()->create(['role' => 'participant', 'company_id' => $company->id]);

        $survey = Survey::create([
            'company_id' => $company->id,
            'title' => 'Alex CQ Evaluation Survey',
            'status' => 'published',
            'published_at' => now(),
            'created_by' => $admin->id,
        ]);
        $survey->participants()->attach([$alex->id, $peer1->id]);

        // 10 questions with 8 and 1 with 9 (average = 8.1)
        // Peer ratings: 9 with 6 and 2 with 8 (average = 6.4)
        // Moderated scores: (8+6)/2 = 7.0 for 9 questions, (8+8)/2 = 8.0, (9+8)/2 = 8.5
        // Total moderated avg = 6.9 -> Driver category (6.1 - 8.0)
        $questions = [];
        for ($i = 1; $i <= 11; $i++) {
            $questions[] = Question::create([
                'survey_id' => $survey->id,
                'question_text' => "Question {$i}",
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }

        // Alex self ratings: 10 x 8, 1 x 9 -> Avg = 8.1
        $selfAssessment = Assessment::create([
            'survey_id' => $survey->id,
            'assessor_id' => $alex->id,
            'subject_id' => $alex->id,
            'status' => 'completed',
            'started_at' => now(),
            'completed_at' => now(),
            'total_score' => 89,
            'max_score' => 110,
            'percentage' => 80.9,
            'category' => 'Initiator',
        ]);
        for ($i = 0; $i < 11; $i++) {
            $score = ($i === 0) ? 9 : 8;
            AssessmentAnswer::create(['assessment_id' => $selfAssessment->id, 'question_id' => $questions[$i]->id, 'score' => $score]);
        }

        // Peer ratings: 9 x 6, 2 x 8 -> 54 + 16 = 70 / 11 = 6.4
        $peerAssessment = Assessment::create([
            'survey_id' => $survey->id,
            'assessor_id' => $peer1->id,
            'subject_id' => $alex->id,
            'status' => 'completed',
            'started_at' => now(),
            'completed_at' => now(),
            'total_score' => 70,
            'max_score' => 110,
            'percentage' => 63.6,
            'category' => 'Initiator',
        ]);
        for ($i = 0; $i < 11; $i++) {
            $score = ($i < 2) ? 8 : 6;
            AssessmentAnswer::create(['assessment_id' => $peerAssessment->id, 'question_id' => $questions[$i]->id, 'score' => $score]);
        }

        $response = $this->actingAs($alex)->get(route('participant.assessments.report', $survey));
        $response->assertOk();

        // Check Change Driver profile details
        $response->assertSee('Alex Driver');
        $response->assertSee('Change Driver');
        $response->assertSee('Driver');
        $response->assertSee('6.1 – 8.0');
        $response->assertSee('6.9'); // Overall CQ score

        // Check Self and Peer score donut cards
        $response->assertSee('8.1');
        $response->assertSee('6.4');

        // Check Perception Gap diagnosis
        $response->assertSee('Perception Gap');
        $response->assertSee('You see yourself stronger than others currently experience');
    }

    public function test_assessment_max_score_excludes_group_sync_questions_and_admin_people_profile_aligns_with_cq_report(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $alex = User::factory()->create(['name' => 'Alex Person', 'role' => 'participant']);
        $bob = User::factory()->create(['name' => 'Bob Colleague', 'role' => 'participant']);

        $survey = Survey::create([
            'title' => 'Leadership CQ Survey',
            'status' => 'published',
            'created_by' => $admin->id,
        ]);
        $survey->participants()->attach([$alex->id, $bob->id]);

        // 11 individual questions + 3 group sync questions
        for ($i = 1; $i <= 11; $i++) {
            Question::create([
                'survey_id' => $survey->id,
                'question_text' => "Individual Question {$i}",
                'type' => 'individual',
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }
        for ($i = 1; $i <= 3; $i++) {
            Question::create([
                'survey_id' => $survey->id,
                'question_text' => "Group Sync Question {$i}",
                'type' => 'group_sync',
                'sort_order' => 11 + $i,
                'is_active' => true,
            ]);
        }

        $this->assertEquals(14, $survey->questions()->count());

        // Alex Self Assessment (ratings of 9: total = 99)
        $selfAssessment = Assessment::create([
            'survey_id' => $survey->id,
            'assessor_id' => $alex->id,
            'subject_id' => $alex->id,
            'status' => 'completed',
        ]);
        $indQuestions = $survey->questions()->where('type', 'individual')->get();
        foreach ($indQuestions as $q) {
            AssessmentAnswer::create(['assessment_id' => $selfAssessment->id, 'question_id' => $q->id, 'score' => 9]);
        }

        // Bob Peer Assessment of Alex (ratings of 9: total = 99)
        $peerAssessment = Assessment::create([
            'survey_id' => $survey->id,
            'assessor_id' => $bob->id,
            'subject_id' => $alex->id,
            'status' => 'completed',
        ]);
        foreach ($indQuestions as $q) {
            AssessmentAnswer::create(['assessment_id' => $peerAssessment->id, 'question_id' => $q->id, 'score' => 9]);
        }

        $scoreService = app(AssessmentScoreService::class);
        $selfResult = $scoreService->calculateAssessmentScore($selfAssessment);
        $peerResult = $scoreService->calculateAssessmentScore($peerAssessment);

        // Crucial: max_score must be 110, NOT 140!
        $this->assertEquals(110, $selfResult['max_score']);
        $this->assertEquals(110, $peerResult['max_score']);
        $this->assertEquals(90.0, $selfResult['percentage']); // 99 / 110 * 100 = 90%
        $this->assertEquals('Achiever', $selfResult['category']);

        // Admin visiting Alex profile should see Achiever and CQ Executive Synthesis
        $response = $this->actingAs($admin)->get(route('admin.people.show', $alex));
        $response->assertOk();
        $response->assertSee('Change Quotient (CQ) Executive Synthesis');
        $response->assertSee('Achiever');
        $response->assertSee('Inspect Full CQ Report');

        // Admin can inspect Alex CQ report via user_id
        $reportResponse = $this->actingAs($admin)->get(route('participant.assessments.report', [$survey, 'user_id' => $alex->id]));
        $reportResponse->assertOk();
        $reportResponse->assertSee('Alex Person');
        $reportResponse->assertSee('Achiever');
    }
}
