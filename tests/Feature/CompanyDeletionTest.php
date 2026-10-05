<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Question;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_company_and_cascades_surveys_and_employees(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $company = Company::factory()->create(['name' => 'Acme Technologies']);

        $employee1 = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'participant',
            'name' => 'Alice Employee',
        ]);

        $employee2 = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'participant',
            'name' => 'Bob Employee',
        ]);

        $companyAdmin = User::factory()->create([
            'company_id' => $company->id,
            'role' => 'admin',
            'name' => 'Company Admin User',
        ]);

        $survey = Survey::create([
            'company_id' => $company->id,
            'title' => 'Annual 360 Review',
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        $question = Question::create([
            'survey_id' => $survey->id,
            'question_text' => 'Demonstrates accountability?',
            'sort_order' => 1,
        ]);

        $survey->participants()->attach([$employee1->id, $employee2->id]);

        $response = $this->actingAs($admin)->delete(route('admin.companies.destroy', $company));

        $response->assertRedirect(route('admin.companies.index'));
        $response->assertSessionHas('success');

        // Company is deleted
        $this->assertDatabaseMissing('companies', ['id' => $company->id]);

        // Survey & Questions are deleted
        $this->assertDatabaseMissing('surveys', ['id' => $survey->id]);
        $this->assertDatabaseMissing('questions', ['id' => $question->id]);

        // Non-admin employees belonging to this company are deleted
        $this->assertDatabaseMissing('users', ['id' => $employee1->id]);
        $this->assertDatabaseMissing('users', ['id' => $employee2->id]);

        // Admin user is NOT deleted, but company_id is cleared
        $this->assertDatabaseHas('users', [
            'id' => $companyAdmin->id,
            'company_id' => null,
        ]);
    }

    public function test_non_admin_cannot_delete_company(): void
    {
        $company = Company::factory()->create(['name' => 'Protected Corp']);
        $participant = User::factory()->create(['role' => 'participant']);

        $response = $this->actingAs($participant)->delete(route('admin.companies.destroy', $company));

        $response->assertForbidden();
        $this->assertDatabaseHas('companies', ['id' => $company->id]);
    }

    public function test_company_views_render_delete_buttons(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $company = Company::factory()->create(['name' => 'Umbrella Corp']);

        // Index view
        $indexRes = $this->actingAs($admin)->get(route('admin.companies.index'));
        $indexRes->assertOk();
        $indexRes->assertSee(route('admin.companies.destroy', $company));
        $indexRes->assertSee('data-confirm-title="Delete Company"', false);

        // Show view
        $showRes = $this->actingAs($admin)->get(route('admin.companies.show', $company));
        $showRes->assertOk();
        $showRes->assertSee(route('admin.companies.destroy', $company));
        $showRes->assertSee('data-confirm-title="Delete Company"', false);

        // Edit view
        $editRes = $this->actingAs($admin)->get(route('admin.companies.edit', $company));
        $editRes->assertOk();
        $editRes->assertSee(route('admin.companies.destroy', $company));
        $editRes->assertSee('Delete Company');
    }
}
