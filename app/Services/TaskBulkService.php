<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskBulkService
{
    /**
     * Atualiza múltiplas tarefas em uma única transação atômica.
     *
     * @param  array<int, array<string, mixed>>  $tasksData Array de tarefas com id e campos para atualizar
     * @param  Project  $project Projeto ao qual as tarefas pertencem
     * @return array<int, Task> Array de tarefas atualizadas
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function bulkUpdate(array $tasksData, Project $project): array
    {
        return DB::transaction(function () use ($tasksData, $project) {
            $updatedTasks = [];

            // Validar existência de todas as tarefas antes de atualizar
            $taskIds = array_column($tasksData, 'id');
            $existingTasks = Task::whereIn('id', $taskIds)
                ->where('project_id', $project->id)
                ->get()
                ->keyBy('id');

            // Verificar se todas as tarefas existem e pertencem ao projeto
            $missingTaskIds = array_diff($taskIds, $existingTasks->keys()->toArray());
            if (! empty($missingTaskIds)) {
                Log::warning('Bulk update failed: some tasks not found or do not belong to project', [
                    'project_id' => $project->id,
                    'missing_task_ids' => $missingTaskIds,
                ]);

                throw \Illuminate\Validation\ValidationException::withMessages([
                    'tasks' => ['Uma ou mais tarefas não foram encontradas ou não pertencem ao projeto.'],
                ]);
            }

            // Atualizar cada tarefa
            foreach ($tasksData as $taskData) {
                $taskId = (int) $taskData['id'];
                $task = $existingTasks->get($taskId);

                if (! $task) {
                    continue;
                }

                // Preparar dados para atualização (remover id do array)
                $updateData = $taskData;
                unset($updateData['id']);

                // Mapear 'position' para 'order_in_phase' se presente
                if (isset($updateData['position'])) {
                    $updateData['order_in_phase'] = $updateData['position'];
                    unset($updateData['position']);
                }

                // Atualizar apenas campos que foram fornecidos
                $task->fill($updateData);
                $task->save();

                $updatedTasks[] = $task;
            }

            // Recarregar relacionamentos para todas as tarefas atualizadas
            $updatedTaskIds = array_map(fn ($task) => $task->id, $updatedTasks);
            $tasksWithRelations = Task::whereIn('id', $updatedTaskIds)
                ->with(['phase', 'assignee', 'contractor'])
                ->get()
                ->keyBy('id');

            // Retornar tarefas na mesma ordem do input
            $result = [];
            foreach ($updatedTasks as $task) {
                $result[] = $tasksWithRelations->get($task->id) ?? $task;
            }

            return $result;
        });
    }
}

