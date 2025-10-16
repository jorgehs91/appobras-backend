<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectsTest extends TestCase
{
    use RefreshDatabase;

    public function test_listar_projects_por_company_com_e_sem_project_header(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'C1']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $p1 = Project::query()->create(['company_id' => $company->id, 'name' => 'P1']);
        $p2 = Project::query()->create(['company_id' => $company->id, 'name' => 'P2']);

        // Sem X-Project-Id -> retorna todos da company
        $this->getJson('/api/v1/projects', ['X-Company-Id' => $company->id])
            ->assertOk()
            ->assertJsonCount(2, 'data');

        // Com X-Project-Id -> filtra
        $this->getJson('/api/v1/projects', ['X-Company-Id' => $company->id, 'X-Project-Id' => $p1->id])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $p1->id);
    }

    public function test_criar_project(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'C1']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/projects', [
            'name' => 'Meu Projeto',
            'description' => 'Desc',
        ], ['X-Company-Id' => $company->id])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Meu Projeto')
            ->assertJsonPath('data.company_id', $company->id);

        $this->assertDatabaseHas('projects', [
            'company_id' => $company->id,
            'name' => 'Meu Projeto',
        ]);
    }

    public function test_bloqueia_acesso_sem_company_context(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/projects')
            ->assertStatus(403);
    }

    public function test_switch_project(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'C1']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $p1 = Project::query()->create(['company_id' => $company->id, 'name' => 'P1']);
        $p2 = Project::query()->create(['company_id' => $company->id, 'name' => 'P2']);

        $this->postJson('/api/v1/me/switch-project', [
            'project_id' => $p2->id,
        ], ['X-Company-Id' => $company->id])
            ->assertOk();

        $user->refresh();
        $this->assertEquals($p2->id, $user->current_project_id);
    }
}


