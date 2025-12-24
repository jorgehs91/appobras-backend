<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Contractor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ContractorsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_contractors_for_company(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        Contractor::factory()->count(3)->create(['company_id' => $company->id]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->getJson('/api/v1/contractors');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_contractor(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson('/api/v1/contractors', [
                'name' => 'Test Contractor',
                'contact' => '(11) 98765-4321',
                'specialties' => 'Fundação, Estrutura',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Test Contractor');

        $this->assertDatabaseHas('contractors', [
            'name' => 'Test Contractor',
            'company_id' => $company->id,
        ]);
    }

    public function test_can_update_contractor(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $contractor = Contractor::factory()->create([
            'company_id' => $company->id,
            'name' => 'Old Name',
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->putJson("/api/v1/contractors/{$contractor->id}", [
                'name' => 'Updated Name',
                'contact' => '(11) 99999-9999',
                'specialties' => 'Alvenaria, Acabamento',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.contact', '(11) 99999-9999');

        $this->assertDatabaseHas('contractors', [
            'id' => $contractor->id,
            'name' => 'Updated Name',
            'company_id' => $company->id,
        ]);
    }

    public function test_can_delete_contractor(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $contractor = Contractor::factory()->create(['company_id' => $company->id]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->deleteJson("/api/v1/contractors/{$contractor->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('contractors', [
            'id' => $contractor->id,
        ]);
    }

    public function test_cannot_update_contractor_from_different_company(): void
    {
        $user = User::factory()->create();
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        $user->companies()->attach($company1->id);
        Sanctum::actingAs($user);

        $contractor = Contractor::factory()->create(['company_id' => $company2->id]);

        $response = $this->withHeader('X-Company-Id', $company1->id)
            ->putJson("/api/v1/contractors/{$contractor->id}", [
                'name' => 'Hacked Name',
            ]);

        $response->assertStatus(403);
    }

    public function test_cannot_delete_contractor_from_different_company(): void
    {
        $user = User::factory()->create();
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        $user->companies()->attach($company1->id);
        Sanctum::actingAs($user);

        $contractor = Contractor::factory()->create(['company_id' => $company2->id]);

        $response = $this->withHeader('X-Company-Id', $company1->id)
            ->deleteJson("/api/v1/contractors/{$contractor->id}");

        $response->assertStatus(403);
    }
}
