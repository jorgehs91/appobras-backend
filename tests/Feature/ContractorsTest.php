<?php

use App\Models\Company;
use App\Models\Contractor;
use App\Models\User;

test('can list contractors for company', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $user->companies()->attach($company->id, ['role' => 'Admin']);
    
    Contractor::factory()->count(3)->create(['company_id' => $company->id]);
    
    $response = $this->actingAs($user)
        ->withHeader('X-Company-Id', $company->id)
        ->getJson('/api/v1/contractors');
    
    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('can create contractor', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $user->companies()->attach($company->id, ['role' => 'Admin']);
    
    $response = $this->actingAs($user)
        ->withHeader('X-Company-Id', $company->id)
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
});

