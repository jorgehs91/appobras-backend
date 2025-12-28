<?php

namespace Tests\Feature;

use App\Enums\ProjectMemberRole;
use App\Jobs\SendProjectInvitationJob;
use App\Models\Company;
use App\Models\Project;
use App\Models\ProjectInvite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProjectMembersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
    }

    public function test_can_list_project_members(): void
    {
        $admin = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $admin->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $admin->assignRole(Role::findByName('Admin Obra', 'sanctum'));

        $project = Project::factory()->create(['company_id' => $company->id]);
        $member1 = User::factory()->create();
        $member2 = User::factory()->create();
        $member1->companies()->attach($company->id);
        $member2->companies()->attach($company->id);

        $project->users()->attach($member1->id, ['role' => ProjectMemberRole::Manager->value, 'joined_at' => now()]);
        $project->users()->attach($member2->id, ['role' => ProjectMemberRole::Engenheiro->value, 'joined_at' => now()]);

        Sanctum::actingAs($admin);

        $response = $this->getJson("/api/v1/projects/{$project->id}/members", [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'role',
                        'joined_at',
                        'preferences',
                    ],
                ],
            ]);

        // Verificar que ambos os membros estão na lista (ordem pode variar)
        $roles = collect($response->json('data'))->pluck('role')->toArray();
        $this->assertContains(ProjectMemberRole::Manager->value, $roles);
        $this->assertContains(ProjectMemberRole::Engenheiro->value, $roles);
    }

    public function test_can_add_member_to_project(): void
    {
        Queue::fake();

        $admin = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $admin->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $admin->assignRole(Role::findByName('Admin Obra', 'sanctum'));

        $project = Project::factory()->create(['company_id' => $company->id]);
        $newMember = User::factory()->create();
        $newMember->companies()->attach($company->id);

        Sanctum::actingAs($admin);

        $response = $this->postJson("/api/v1/projects/{$project->id}/members", [
            'user_id' => $newMember->id,
            'role' => ProjectMemberRole::Engenheiro->value,
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertCreated()
            ->assertJsonStructure([
                'message',
                'invite_token',
                'expires_at',
            ]);

        $response->assertJsonPath('message', 'Convite enviado com sucesso. O usuário será adicionado ao projeto após aceitar o convite.');

        // Verificar que o convite foi criado
        $this->assertDatabaseHas('project_invites', [
            'project_id' => $project->id,
            'user_id' => $newMember->id,
            'role' => ProjectMemberRole::Engenheiro->value,
        ]);

        // Verificar que o membro NÃO foi adicionado ao projeto ainda (só será adicionado ao aceitar)
        $this->assertDatabaseMissing('project_user', [
            'project_id' => $project->id,
            'user_id' => $newMember->id,
        ]);

        // Verificar que o job de convite foi disparado
        Queue::assertPushed(SendProjectInvitationJob::class);
    }

    public function test_can_update_member_role(): void
    {
        $admin = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $admin->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $admin->assignRole(Role::findByName('Admin Obra', 'sanctum'));

        $project = Project::factory()->create(['company_id' => $company->id]);
        $member = User::factory()->create();
        $member->companies()->attach($company->id);
        $project->users()->attach($member->id, ['role' => ProjectMemberRole::Viewer->value, 'joined_at' => now()]);

        Sanctum::actingAs($admin);

        $this->patchJson("/api/v1/projects/{$project->id}/members/{$member->id}", [
            'role' => ProjectMemberRole::Manager->value,
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.role', ProjectMemberRole::Manager->value);

        // Verificar que a role foi atualizada no banco
        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $member->id,
            'role' => ProjectMemberRole::Manager->value,
        ]);
    }

    public function test_can_remove_member_from_project(): void
    {
        $admin = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $admin->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $admin->assignRole(Role::findByName('Admin Obra', 'sanctum'));

        $project = Project::factory()->create(['company_id' => $company->id]);
        $member = User::factory()->create();
        $member->companies()->attach($company->id);
        $project->users()->attach($member->id, ['role' => ProjectMemberRole::Viewer->value, 'joined_at' => now()]);

        Sanctum::actingAs($admin);

        $this->deleteJson("/api/v1/projects/{$project->id}/members/{$member->id}", [], [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJson(['message' => 'Membro removido do projeto com sucesso.']);

        // Verificar que o membro foi removido
        $this->assertDatabaseMissing('project_user', [
            'project_id' => $project->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_cannot_manage_members_without_admin_obra_role(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        // Usuário sem role Admin Obra
        $user->assignRole(Role::findByName('Leitor', 'sanctum'));

        $project = Project::factory()->create(['company_id' => $company->id]);
        $member = User::factory()->create();
        $member->companies()->attach($company->id);

        Sanctum::actingAs($user);

        // Tentar listar membros
        $this->getJson("/api/v1/projects/{$project->id}/members", [
            'X-Company-Id' => $company->id,
        ])
            ->assertForbidden();

        // Tentar adicionar membro
        $this->postJson("/api/v1/projects/{$project->id}/members", [
            'user_id' => $member->id,
            'role' => ProjectMemberRole::Viewer->value,
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertForbidden();

        // Tentar atualizar role
        $project->users()->attach($member->id, ['role' => ProjectMemberRole::Viewer->value]);
        $this->patchJson("/api/v1/projects/{$project->id}/members/{$member->id}", [
            'role' => ProjectMemberRole::Manager->value,
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertForbidden();

        // Tentar remover membro
        $this->deleteJson("/api/v1/projects/{$project->id}/members/{$member->id}", [], [
            'X-Company-Id' => $company->id,
        ])
            ->assertForbidden();
    }

    public function test_cannot_add_duplicate_member(): void
    {
        $admin = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $admin->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $admin->assignRole(Role::findByName('Admin Obra', 'sanctum'));

        $project = Project::factory()->create(['company_id' => $company->id]);
        $member = User::factory()->create();
        $member->companies()->attach($company->id);
        $project->users()->attach($member->id, ['role' => ProjectMemberRole::Viewer->value, 'joined_at' => now()]);

        Sanctum::actingAs($admin);

        $this->postJson("/api/v1/projects/{$project->id}/members", [
            'user_id' => $member->id,
            'role' => ProjectMemberRole::Engenheiro->value,
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertUnprocessable()
            ->assertJson(['message' => 'O usuário já é membro deste projeto.']);
    }

    public function test_member_list_includes_role_and_joined_at(): void
    {
        $admin = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $admin->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $admin->assignRole(Role::findByName('Admin Obra', 'sanctum'));

        $project = Project::factory()->create(['company_id' => $company->id]);
        $member = User::factory()->create();
        $member->companies()->attach($company->id);
        $joinedAt = now()->subDays(5);
        $project->users()->attach($member->id, [
            'role' => ProjectMemberRole::Fiscal->value,
            'joined_at' => $joinedAt,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson("/api/v1/projects/{$project->id}/members", [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $response->assertJsonPath('data.0.role', ProjectMemberRole::Fiscal->value);
        // Verificar que joined_at está presente e é uma data válida (pode ter diferença de precisão)
        $this->assertNotNull($response->json('data.0.joined_at'));
        $this->assertStringStartsWith($joinedAt->format('Y-m-d\TH:i:s'), $response->json('data.0.joined_at'));
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'joined_at',
                    'preferences',
                ],
            ],
        ]);
    }

    public function test_cannot_manage_members_from_different_company(): void
    {
        $admin = User::factory()->create();
        $company1 = Company::query()->create(['name' => 'Company 1']);
        $company2 = Company::query()->create(['name' => 'Company 2']);
        $admin->companies()->attach($company1->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company1->id);
        $admin->assignRole(Role::findByName('Admin Obra', 'sanctum'));

        $project = Project::factory()->create(['company_id' => $company2->id]);
        $member = User::factory()->create();
        $member->companies()->attach($company2->id);

        Sanctum::actingAs($admin);

        // Tentar acessar projeto de outra empresa
        $this->getJson("/api/v1/projects/{$project->id}/members", [
            'X-Company-Id' => $company1->id,
        ])
            ->assertForbidden();

        // Tentar adicionar membro em projeto de outra empresa
        $this->postJson("/api/v1/projects/{$project->id}/members", [
            'user_id' => $member->id,
            'role' => ProjectMemberRole::Viewer->value,
        ], [
            'X-Company-Id' => $company1->id,
        ])
            ->assertForbidden();
    }

    public function test_validates_role_enum(): void
    {
        $admin = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $admin->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $admin->assignRole(Role::findByName('Admin Obra', 'sanctum'));

        $project = Project::factory()->create(['company_id' => $company->id]);
        $member = User::factory()->create();
        $member->companies()->attach($company->id);

        Sanctum::actingAs($admin);

        // Tentar adicionar com role inválida
        $this->postJson("/api/v1/projects/{$project->id}/members", [
            'user_id' => $member->id,
            'role' => 'InvalidRole',
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['role']);

        // Tentar atualizar com role inválida
        $project->users()->attach($member->id, ['role' => ProjectMemberRole::Viewer->value]);
        $this->patchJson("/api/v1/projects/{$project->id}/members/{$member->id}", [
            'role' => 'AnotherInvalidRole',
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['role']);
    }

    public function test_project_invite_is_created_when_adding_member(): void
    {
        Queue::fake();

        $admin = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $admin->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $admin->assignRole(Role::findByName('Admin Obra', 'sanctum'));

        $project = Project::factory()->create(['company_id' => $company->id]);
        $member = User::factory()->create();
        $member->companies()->attach($company->id);

        Sanctum::actingAs($admin);

        $this->postJson("/api/v1/projects/{$project->id}/members", [
            'user_id' => $member->id,
            'role' => ProjectMemberRole::Engenheiro->value,
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertCreated();

        // Verificar que o convite foi criado
        $this->assertDatabaseHas('project_invites', [
            'project_id' => $project->id,
            'user_id' => $member->id,
            'role' => ProjectMemberRole::Engenheiro->value,
        ]);

        $invite = ProjectInvite::query()
            ->where('project_id', $project->id)
            ->where('user_id', $member->id)
            ->first();

        $this->assertNotNull($invite);
        $this->assertNotNull($invite->token);
        $this->assertNull($invite->accepted_at);

        // Verificar que o membro NÃO foi adicionado ao projeto ainda
        $this->assertDatabaseMissing('project_user', [
            'project_id' => $project->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_can_accept_project_invite(): void
    {
        $admin = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $admin->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $admin->assignRole(Role::findByName('Admin Obra', 'sanctum'));

        $project = Project::factory()->create(['company_id' => $company->id]);
        $member = User::factory()->create();
        $member->companies()->attach($company->id);

        // Criar convite
        $invite = ProjectInvite::query()->create([
            'project_id' => $project->id,
            'user_id' => $member->id,
            'role' => ProjectMemberRole::Fiscal->value,
            'token' => 'test-token-123',
            'expires_at' => now()->addDays(7),
        ]);

        Sanctum::actingAs($member);

        // Aceitar convite
        $this->postJson("/api/v1/invites/project/{$invite->token}/accept")
            ->assertOk()
            ->assertJson(['message' => 'Project invite accepted']);

        // Verificar que o membro foi adicionado ao projeto
        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $member->id,
            'role' => ProjectMemberRole::Fiscal->value,
        ]);

        // Verificar que o convite foi marcado como aceito
        $invite->refresh();
        $this->assertNotNull($invite->accepted_at);
    }
}

