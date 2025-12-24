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
        // usuário é membro apenas de P1
        $user->projects()->attach($p1->id, ['role' => 'Viewer']);

        // Sem X-Project-Id -> retorna apenas os projetos onde o usuário é membro
        $this->getJson('/api/v1/projects', ['X-Company-Id' => $company->id])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $p1->id);

        // Com X-Project-Id -> filtra e respeita membership
        $this->getJson('/api/v1/projects', ['X-Company-Id' => $company->id, 'X-Project-Id' => $p1->id])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $p1->id);

        // Com X-Project-Id de um projeto que não é membro -> vazio
        $this->getJson('/api/v1/projects', ['X-Company-Id' => $company->id, 'X-Project-Id' => $p2->id])
            ->assertOk()
            ->assertJsonCount(0, 'data');
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

        $project = Project::query()->where('company_id', $company->id)->where('name', 'Meu Projeto')->first();
        $this->assertNotNull($project);
        $this->assertEquals($user->id, $project->manager_user_id);

        // membership do criador como Manager
        $this->assertTrue($user->projects()->whereKey($project->id)->exists());
        $pivot = $user->projects()->whereKey($project->id)->first()->pivot;
        $this->assertEquals('Manager', $pivot->role);
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

        // torna-se membro do projeto alvo
        $user->projects()->attach($p2->id, ['role' => 'Viewer']);

        $this->postJson('/api/v1/me/switch-project', [
            'project_id' => $p2->id,
        ], ['X-Company-Id' => $company->id])
            ->assertOk();

        $user->refresh();
        $this->assertEquals($p2->id, $user->current_project_id);
    }

    public function test_switch_project_bloqueia_quando_nao_membro(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'C1']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $p = Project::query()->create(['company_id' => $company->id, 'name' => 'P1']);

        // sem membership
        $this->postJson('/api/v1/me/switch-project', [
            'project_id' => $p->id,
        ], ['X-Company-Id' => $company->id])
            ->assertStatus(403);
    }

    public function test_update_para_completed_define_actual_end_date_quando_vazio(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'C1']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $p = Project::query()->create([
            'company_id' => $company->id,
            'name' => 'P1',
            'status' => 'in_progress',
            'start_date' => now()->subDays(10)->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
        ]);
        // membership
        $user->projects()->attach($p->id, ['role' => 'Viewer']);

        $this->patchJson('/api/v1/projects/'.$p->id, [
            'status' => 'completed',
        ], ['X-Company-Id' => $company->id])
            ->assertOk();

        $p->refresh();
        $this->assertEquals('completed', $p->status->value);
        $this->assertNotNull($p->actual_end_date);
    }

    public function test_show_project_sucesso_quando_membro(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'C1']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $p = Project::query()->create([
            'company_id' => $company->id,
            'name' => 'P1',
        ]);
        $user->projects()->attach($p->id, ['role' => 'Viewer']);

        $this->getJson('/api/v1/projects/'.$p->id, ['X-Company-Id' => $company->id])
            ->assertOk()
            ->assertJsonPath('data.id', $p->id);
    }

    public function test_show_project_403_quando_nao_membro(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'C1']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $p = Project::query()->create([
            'company_id' => $company->id,
            'name' => 'P1',
        ]);

        $this->getJson('/api/v1/projects/'.$p->id, ['X-Company-Id' => $company->id])
            ->assertStatus(403);
    }

    public function test_project_progress_shows_phases_breakdown(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'C1']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $p = Project::query()->create([
            'company_id' => $company->id,
            'name' => 'P1',
        ]);
        $user->projects()->attach($p->id, ['role' => 'Viewer']);

        // Create active phases
        \App\Models\Phase::factory()->count(2)->create([
            'company_id' => $company->id,
            'project_id' => $p->id,
            'status' => 'active',
        ]);

        $this->getJson('/api/v1/projects/'.$p->id.'/progress', ['X-Company-Id' => $company->id])
            ->assertOk()
            ->assertJsonStructure([
                'project_id',
                'project_progress_percent',
                'phases' => [
                    '*' => ['id', 'name', 'status', 'progress_percent', 'tasks_count'],
                ],
            ])
            ->assertJsonPath('project_id', $p->id);
    }

    public function test_project_progress_only_shows_active_phases(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'C1']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $p = Project::query()->create([
            'company_id' => $company->id,
            'name' => 'P1',
        ]);
        $user->projects()->attach($p->id, ['role' => 'Viewer']);

        // Create 2 active phases and 1 archived
        \App\Models\Phase::factory()->count(2)->create([
            'company_id' => $company->id,
            'project_id' => $p->id,
            'status' => 'active',
        ]);

        \App\Models\Phase::factory()->create([
            'company_id' => $company->id,
            'project_id' => $p->id,
            'status' => 'archived',
        ]);

        $response = $this->getJson('/api/v1/projects/'.$p->id.'/progress', ['X-Company-Id' => $company->id])
            ->assertOk();

        // Should only return the 2 active phases
        $this->assertCount(2, $response->json('phases'));
    }

    public function test_cannot_view_progress_without_project_membership(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'C1']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $p = Project::query()->create([
            'company_id' => $company->id,
            'name' => 'P1',
        ]);
        // User is NOT a member of this project

        $this->getJson('/api/v1/projects/'.$p->id.'/progress', ['X-Company-Id' => $company->id])
            ->assertStatus(403);
    }
}


