<?php

use App\Models\Company;
use App\Models\Phase;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

test('can list phases for project', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $user->companies()->attach($company->id, ['role' => 'Admin']);
    
    $project = Project::factory()->create(['company_id' => $company->id]);
    $user->projects()->attach($project->id, ['role' => 'Manager']);
    
    Phase::factory()->count(3)->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
    ]);
    
    $response = $this->actingAs($user)
        ->withHeader('X-Company-Id', $company->id)
        ->getJson("/api/v1/projects/{$project->id}/phases");
    
    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('phase progress is calculated correctly', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $user->companies()->attach($company->id, ['role' => 'Admin']);
    
    $project = Project::factory()->create(['company_id' => $company->id]);
    $user->projects()->attach($project->id, ['role' => 'Manager']);
    
    $phase = Phase::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
        'status' => 'active',
    ]);
    
    // Create tasks with different statuses
    Task::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
        'phase_id' => $phase->id,
        'status' => 'done', // 100%
    ]);
    
    Task::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
        'phase_id' => $phase->id,
        'status' => 'in_progress', // 50%
    ]);
    
    $phase->refresh();
    
    // Progress should be (100 + 50) / 2 = 75
    expect($phase->progress_percent)->toBe(75);
});

test('project progress is calculated correctly', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $user->companies()->attach($company->id, ['role' => 'Admin']);
    
    $project = Project::factory()->create(['company_id' => $company->id]);
    $user->projects()->attach($project->id, ['role' => 'Manager']);
    
    // Create 2 active phases
    $phase1 = Phase::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
        'status' => 'active',
    ]);
    
    $phase2 = Phase::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
        'status' => 'active',
    ]);
    
    // Phase 1: 100% done
    Task::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
        'phase_id' => $phase1->id,
        'status' => 'done',
    ]);
    
    // Phase 2: 50% done
    Task::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
        'phase_id' => $phase2->id,
        'status' => 'in_progress',
    ]);
    
    $project->refresh();
    
    // Project progress should be (100 + 50) / 2 = 75
    expect($project->progress_percent)->toBe(75);
});

test('task observer sets timestamps correctly', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $project = Project::factory()->create(['company_id' => $company->id]);
    $phase = Phase::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
    ]);
    
    $task = Task::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
        'phase_id' => $phase->id,
        'status' => 'backlog',
        'started_at' => null,
        'completed_at' => null,
    ]);
    
    // Move to in_progress - should set started_at
    $task->status = 'in_progress';
    $task->save();
    $task->refresh();
    
    expect($task->started_at)->not->toBeNull();
    
    // Move to done - should set completed_at
    $task->status = 'done';
    $task->save();
    $task->refresh();
    
    expect($task->completed_at)->not->toBeNull();
    
    // Reopen task - should clear completed_at
    $task->status = 'in_progress';
    $task->save();
    $task->refresh();
    
    expect($task->completed_at)->toBeNull();
});

