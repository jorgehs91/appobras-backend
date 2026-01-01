<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\File;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\File>
 */
class FileFactory extends Factory
{
    protected $model = File::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $extension = fake()->randomElement(['pdf', 'jpg', 'png', 'docx', 'xlsx', 'zip']);
        $fileName = fake()->word() . '.' . $extension;
        $projectFactory = Project::factory();

        return [
            'fileable_type' => Project::class,
            'fileable_id' => $projectFactory,
            'company_id' => Company::factory(),
            'project_id' => $projectFactory, // Mesma factory - será resolvida para o mesmo projeto
            'name' => $fileName,
            'path' => 'files/' . $fileName,
            'url' => '/storage/files/' . $fileName,
            'mime_type' => fake()->mimeType(),
            'size' => fake()->numberBetween(10000, 5000000),
            'thumbnail_path' => null,
            'category' => 'document',
            'description' => null,
            'uploaded_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the file is a document.
     */
    public function document(Project $project = null, User $user = null): static
    {
        return $this->state(function (array $attributes) use ($project, $user) {
            $state = [
                'fileable_type' => Project::class,
                'category' => 'document',
            ];
            
            // Se project foi passado como parâmetro, usar ele
            if ($project) {
                $projectId = $project->id;
                $state['fileable_id'] = $projectId;
                $state['project_id'] = $projectId;
                $state['company_id'] = $project->company_id;
                $state['path'] = 'documents/project-' . $projectId . '/' . ($attributes['name'] ?? fake()->word() . '.pdf');
            }
            // Se project_id foi passado no create() como valor direto (int/string), usar
            elseif (isset($attributes['project_id']) && !is_object($attributes['project_id'])) {
                $projectId = $attributes['project_id'];
                $state['fileable_id'] = $projectId;
                $state['project_id'] = $projectId;
                $state['path'] = 'documents/project-' . $projectId . '/' . ($attributes['name'] ?? fake()->word() . '.pdf');
            }
            // Se fileable_id foi passado no create() como valor direto, usar
            elseif (isset($attributes['fileable_id']) && !is_object($attributes['fileable_id'])) {
                $projectId = $attributes['fileable_id'];
                $state['fileable_id'] = $projectId;
                $state['project_id'] = $projectId;
                $state['path'] = 'documents/project-' . $projectId . '/' . ($attributes['name'] ?? fake()->word() . '.pdf');
            }
            // Caso contrário, deixar os factories do definition() serem resolvidos
            // O path será genérico, mas os testes devem sempre passar project_id/fileable_id
            else {
                // Não podemos construir o path aqui sem o ID resolvido
                // Os testes devem sempre passar project_id ou fileable_id no create()
            }
            
            if ($user) {
                $state['uploaded_by'] = $user->id;
            }
            
            return $state;
        });
    }

    /**
     * Indicate that the file is an attachment.
     * Note: fileable_id should be set to task_id when using this state.
     */
    public function attachment(): static
    {
        return $this->state(function (array $attributes) {
            // fileable_id should be provided (task_id)
            $taskId = $attributes['fileable_id'] ?? null;
            
            if (!$taskId) {
                // If not provided, create a task (for testing convenience)
                $task = \App\Models\Task::factory()->create();
                $taskId = $task->id;
                $projectId = $task->project_id;
                $companyId = $task->company_id;
            } else {
                $task = \App\Models\Task::find($taskId);
                if (!$task) {
                    throw new \InvalidArgumentException("Task with id {$taskId} not found");
                }
                $projectId = $task->project_id;
                $companyId = $task->company_id;
            }
            
            return [
                'fileable_type' => \App\Models\Task::class,
                'fileable_id' => $taskId,
                'company_id' => $attributes['company_id'] ?? $companyId,
                'project_id' => $attributes['project_id'] ?? $projectId,
                'category' => 'attachment',
                'path' => 'attachments/task-' . $taskId . '/' . ($attributes['name'] ?? fake()->word() . '.pdf'),
                'url' => null,
            ];
        });
    }
}
