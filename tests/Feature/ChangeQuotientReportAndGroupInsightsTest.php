<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\Company;
use App\Models\Question;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChangeQuotientReportAndGroupInsightsTest extends TestCase
{
    use RefreshDatabase;

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
}
