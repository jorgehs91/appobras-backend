<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pode_listar_roles_e_permissions(): void
    {
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $company = \App\Models\Company::query()->create(['name' => 'C1']);
        $admin = User::factory()->create();
        $admin->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $admin->assignRole(Role::findByName('Admin Obra', 'sanctum'));

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/admin/roles', ['X-Company-Id' => $company->id])->assertOk();
        $this->getJson('/api/v1/admin/permissions', ['X-Company-Id' => $company->id])->assertOk();
    }

    public function test_usuario_sem_permissao_recebe_forbidden(): void
    {
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $company = \App\Models\Company::query()->create(['name' => 'C1']);
        $user = User::factory()->create();
        // não vinculado à empresa -> deve 403
        Sanctum::actingAs($user);
        $this->getJson('/api/v1/admin/roles', ['X-Company-Id' => $company->id])->assertForbidden();
    }

    public function test_cache_invalida_apos_atribuir_role(): void
    {
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $company = \App\Models\Company::query()->create(['name' => 'C1']);
        $admin = User::factory()->create();
        $admin->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $admin->assignRole('Admin Obra');

        $target = User::factory()->create();
        $target->companies()->attach($company->id);
        $role = Role::findByName('Leitor', 'sanctum');

        Sanctum::actingAs($admin);
        $this->postJson('/api/v1/admin/roles/'.$role->id.'/assign', ['user_id' => $target->id], ['X-Company-Id' => $company->id])->assertOk();

        $target->refresh();
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $this->assertTrue($target->hasRole('Leitor'));
        $this->assertTrue($target->can('projects.view'));
        // verifica vínculo na company_user
        $this->assertTrue($target->companies()->whereKey($company->id)->exists());
    }
}


