<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\Question;
use App\Models\ScoreCategory;
use App\Models\Survey;
use App\Models\User;
use App\Services\AssessmentCategoryService;
use App\Services\AssessmentGenerationService;
use App\Services\AssessmentScoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $participant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Admin User',
            'email' => 'admin@test.com',
        ]);

        $this->participant = User::factory()->create([
            'role' => 'participant',
            'name' => 'John Participant',
            'email' => 'john@test.com',
        ]);
    }

    public function test_admin_can_view_categories_list(): void
    {
        $category = ScoreCategory::create([
            'name' => 'Cucumber',
            'min_percentage' => 80.01,
            'max_percentage' => 100.00,
            'emoji' => '🥒',
            'color' => '#059669',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.categories.index'));
        $response->assertOk();
        $response->assertSee('Score Categories');
        $response->assertSee('Cucumber');
    }

    public function test_admin_can_create_new_category(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name' => 'Dragonfruit',
            'min_percentage' => 90.00,
            'max_percentage' => 100.00,
            'emoji' => '🐉',
            'color' => '#EC4899',
            'description' => 'Elite top performers tier',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('score_categories', [
            'name' => 'Dragonfruit',
            'emoji' => '🐉',
            'color' => '#EC4899',
        ]);
    }

    public function test_admin_can_edit_existing_category(): void
    {
        $category = ScoreCategory::create([
            'name' => 'Tomato',
            'min_percentage' => 40.01,
            'max_percentage' => 60.00,
            'emoji' => '🍅',
            'color' => '#EF4444',
        ]);

        $editPage = $this->actingAs($this->admin)->get(route('admin.categories.edit', $category));
        $editPage->assertOk();
        $editPage->assertSee('Tomato');

        $response = $this->actingAs($this->admin)->put(route('admin.categories.update', $category), [
            'name' => 'Red Tomato',
            'min_percentage' => 40.01,
            'max_percentage' => 60.00,
            'emoji' => '🍅',
            'color' => '#DC2626',
            'description' => 'Updated tier description',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('score_categories', [
            'id' => $category->id,
            'name' => 'Red Tomato',
            'color' => '#DC2626',
        ]);
    }

    public function test_admin_can_delete_category(): void
    {
        $category = ScoreCategory::create([
            'name' => 'Temporary Tier',
            'min_percentage' => 0.00,
            'max_percentage' => 10.00,
            'emoji' => '🧪',
            'color' => '#6B7280',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category));
        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseMissing('score_categories', [
            'id' => $category->id,
        ]);
    }

    public function test_admin_can_reset_categories_to_defaults(): void
    {
        ScoreCategory::truncate();
        $this->assertEquals(0, ScoreCategory::count());

        $response = $this->actingAs($this->admin)->post(route('admin.categories.reset-defaults'));
        $response->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('score_categories', ['name' => 'Apple']);
        $this->assertDatabaseHas('score_categories', ['name' => 'Orange']);
        $this->assertDatabaseHas('score_categories', ['name' => 'Tomato']);
        $this->assertDatabaseHas('score_categories', ['name' => 'Lemon']);
        $this->assertDatabaseHas('score_categories', ['name' => 'Cucumber']);
        $this->assertEquals(5, ScoreCategory::count());
    }

    public function test_assessment_category_service_uses_custom_category_from_database(): void
    {
        ScoreCategory::truncate();
        ScoreCategory::create([
            'name' => 'Gold Star',
            'min_percentage' => 85.00,
            'max_percentage' => 100.00,
            'emoji' => '⭐',
            'color' => '#F59E0B',
        ]);

        AssessmentCategoryService::clearCache();
        $service = app(AssessmentCategoryService::class);

        $this->assertEquals('Gold Star', $service->getCategory(92.5));
        $this->assertEquals('⭐', $service->getEmoji('Gold Star'));
        $this->assertEquals('#F59E0B', $service->getColorHex('Gold Star'));
    }

    public function test_admin_can_recalculate_scores_with_new_categories(): void
    {
        $survey = Survey::create([
            'title' => 'Leadership Survey',
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);

        $q = Question::create([
            'survey_id' => $survey->id,
            'question_text' => 'Good leader?',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $survey->participants()->attach([$this->participant->id]);
        app(AssessmentGenerationService::class)->generateForSurvey($survey);

        $assessment = Assessment::first();
        AssessmentAnswer::create([
            'assessment_id' => $assessment->id,
            'question_id' => $q->id,
            'score' => 10,
        ]);
        $assessment->update(['status' => 'completed']);
        app(AssessmentScoreService::class)->calculateAssessmentScore($assessment);

        // Now change categories in DB
        ScoreCategory::truncate();
        ScoreCategory::create([
            'name' => 'Super Cucumber',
            'min_percentage' => 80.01,
            'max_percentage' => 100.00,
            'emoji' => '🥒',
            'color' => '#059669',
        ]);

        // Post recalculate
        $res = $this->actingAs($this->admin)->post(route('admin.categories.recalculate'));
        $res->assertRedirect(route('admin.categories.index'));

        $assessment->refresh();
        $this->assertEquals('Super Cucumber', $assessment->category);
    }

    public function test_non_admin_cannot_access_categories_crud(): void
    {
        $res = $this->actingAs($this->participant)->get(route('admin.categories.index'));
        $res->assertForbidden();

        $postRes = $this->actingAs($this->participant)->post(route('admin.categories.store'), [
            'name' => 'Hacker Tier',
            'min_percentage' => 0,
            'max_percentage' => 100,
        ]);
        $postRes->assertForbidden();
    }
}
