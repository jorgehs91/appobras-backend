<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SuppliersTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_suppliers(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        Supplier::factory()->count(3)->create();

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->getJson('/api/v1/suppliers');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_show_supplier(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $supplier = Supplier::factory()->create([
            'name' => 'Test Supplier',
            'cnpj' => '12345678000190',
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->getJson("/api/v1/suppliers/{$supplier->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Test Supplier')
            ->assertJsonPath('data.cnpj', '12.345.678/0001-90');
    }

    public function test_can_create_supplier(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson('/api/v1/suppliers', [
                'name' => 'Test Supplier',
                'cnpj' => '12345678000190',
                'contact' => '(11) 98765-4321',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Test Supplier')
            ->assertJsonPath('data.cnpj', '12.345.678/0001-90');

        $this->assertDatabaseHas('suppliers', [
            'name' => 'Test Supplier',
            'cnpj' => '12.345.678/0001-90',
        ]);
    }

    public function test_can_create_supplier_with_formatted_cnpj(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson('/api/v1/suppliers', [
                'name' => 'Test Supplier',
                'cnpj' => '12.345.678/0001-90',
                'contact' => '(11) 98765-4321',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.cnpj', '12.345.678/0001-90');
    }

    public function test_cannot_create_supplier_with_duplicate_cnpj(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        Supplier::factory()->create([
            'cnpj' => '12345678000190',
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson('/api/v1/suppliers', [
                'name' => 'Another Supplier',
                'cnpj' => '12345678000190',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cnpj']);
    }

    public function test_can_update_supplier(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $supplier = Supplier::factory()->create([
            'name' => 'Old Name',
            'cnpj' => '12345678000190',
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->putJson("/api/v1/suppliers/{$supplier->id}", [
                'name' => 'Updated Name',
                'cnpj' => '12345678000190',
                'contact' => '(11) 99999-9999',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.contact', '(11) 99999-9999');

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_can_delete_supplier(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $supplier = Supplier::factory()->create();

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->deleteJson("/api/v1/suppliers/{$supplier->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('suppliers', [
            'id' => $supplier->id,
        ]);
    }

    public function test_cannot_access_suppliers_without_company_header(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/suppliers');

        $response->assertStatus(403);
    }

    public function test_cannot_access_suppliers_without_company_membership(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->getJson('/api/v1/suppliers');

        $response->assertStatus(403);
    }

    public function test_validation_requires_name(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson('/api/v1/suppliers', [
                'cnpj' => '12345678000190',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_validation_requires_cnpj(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson('/api/v1/suppliers', [
                'name' => 'Test Supplier',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cnpj']);
    }

    public function test_validation_rejects_invalid_cnpj_format(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson('/api/v1/suppliers', [
                'name' => 'Test Supplier',
                'cnpj' => '123', // Invalid format
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cnpj']);
    }
}

