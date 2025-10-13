<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyInvite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CompaniesTest extends TestCase
{
    use RefreshDatabase;

    public function test_criar_company_e_definir_ativa(): void
    {
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/companies', ['name' => 'Minha Co'])
            ->assertCreated();

        $user->refresh();
        $this->assertNotNull($user->current_company_id);
        $this->assertCount(1, $user->companies()->get());
    }

    public function test_convidar_e_aceitar_convite(): void
    {
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $admin = User::factory()->create();
        Sanctum::actingAs($admin);
        // cria company
        $company = Company::query()->create(['name' => 'C1']);
        $admin->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $admin->assignRole('Admin Obra');

        // cria convite
        $resp = $this->postJson('/api/v1/companies/'.$company->id.'/invites', [
            'email' => 'guest@example.com',
            'role_name' => 'Leitor',
        ], ['X-Company-Id' => $company->id])->assertCreated();

        $token = $resp->json('token');

        // usuário convidado aceita
        $guest = User::factory()->create(['email' => 'guest@example.com']);
        Sanctum::actingAs($guest);
        $this->postJson('/api/v1/invites/'.$token.'/accept')->assertOk();

        $this->assertTrue($guest->companies()->whereKey($company->id)->exists());
    }

    public function test_switch_company(): void
    {
        $user = User::factory()->create();
        $companyA = Company::query()->create(['name' => 'A']);
        $companyB = Company::query()->create(['name' => 'B']);
        $user->companies()->attach([$companyA->id, $companyB->id]);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/me/switch-company', ['company_id' => $companyB->id])
            ->assertOk();

        $user->refresh();
        $this->assertEquals($companyB->id, $user->current_company_id);
    }
}


