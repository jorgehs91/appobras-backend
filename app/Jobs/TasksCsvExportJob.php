<?php

namespace App\Jobs;

use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Export tasks to CSV format.
 * 
 * Filters: project, phase, status, assignee, period (end date), overdue (yes/no)
 * Fields: id, obra, fase, título, responsável, status, data_início, data_fim, atraso(dias), atualizado_em
 */
class TasksCsvExportJob extends BaseCsvExportJob
{
    /**
     * Get the report type identifier.
     */
    protected function getReportType(): string
    {
        return 'tasks';
    }

    /**
     * Get CSV headers in Portuguese (pt-BR).
     *
     * @return array<int, string>
     */
    protected function getHeaders(): array
    {
        return [
            'ID',
            'Obra',
            'Fase',
            'Título',
            'Responsável',
            'Status',
            'Prioridade',
            'Data Início',
            'Data Fim',
            'Data Vencimento',
            'Atraso (dias)',
            'Iniciado em',
            'Concluído em',
            'Criado em',
            'Atualizado em',
        ];
    }

    /**
     * Process data in chunks and call the callback for each chunk.
     *
     * @param  callable  $callback
     * @param  int  $chunkSize
     */
    protected function processDataInChunks(callable $callback, int $chunkSize): void
    {
        $query = $this->buildQuery();

        $query->chunk($chunkSize, function ($tasks) use ($callback) {
            $callback($tasks);
        });
    }

    /**
     * Format a task row for CSV output.
     *
     * @param  Task  $task
     * @return array<int, mixed>
     */
    protected function formatRow($task): array
    {
        $dueDate = $task->due_at ? Carbon::parse($task->due_at) : null;
        $today = Carbon::today();
        $delayDays = null;

        if ($dueDate && $dueDate->isPast() && $task->status->value !== 'done' && $task->status->value !== 'canceled') {
            // Calculate delay as positive number of days overdue
            // diffInDays returns negative when first date is before second, so we use abs
            $delayDays = abs($today->diffInDays($dueDate, false));
        }

        return [
            $task->id,
            $task->project?->name ?? '',
            $task->phase?->name ?? '',
            $task->title,
            $task->assignee?->name ?? '',
            $this->formatStatus($task->status->value),
            $this->formatPriority($task->priority->value),
            $task->planned_start_at?->format('d/m/Y') ?? '',
            $task->planned_end_at?->format('d/m/Y') ?? '',
            $task->due_at?->format('d/m/Y') ?? '',
            $delayDays !== null ? $delayDays : '',
            $task->started_at?->format('d/m/Y H:i') ?? '',
            $task->completed_at?->format('d/m/Y H:i') ?? '',
            $task->created_at->format('d/m/Y H:i'),
            $task->updated_at->format('d/m/Y H:i'),
        ];
    }

    /**
     * Build the query with filters applied.
     */
    protected function buildQuery(): Builder
    {
        $query = Task::query()
            ->with(['project', 'phase', 'assignee'])
            ->where('company_id', $this->companyId);

        // Filter by project
        if ($this->projectId) {
            $query->where('project_id', $this->projectId);
        } elseif (isset($this->filters['project_id'])) {
            $query->where('project_id', $this->filters['project_id']);
        }

        // Filter by phase
        if (isset($this->filters['phase_id'])) {
            $query->where('phase_id', $this->filters['phase_id']);
        }

        // Filter by status
        if (isset($this->filters['status'])) {
            if (is_array($this->filters['status'])) {
                $query->whereIn('status', $this->filters['status']);
            } else {
                $query->where('status', $this->filters['status']);
            }
        }

        // Filter by assignee
        if (isset($this->filters['assignee_id'])) {
            $query->where('assignee_id', $this->filters['assignee_id']);
        }

        // Filter by period (end date)
        if (isset($this->filters['start_date'])) {
            $query->whereDate('planned_end_at', '>=', $this->filters['start_date']);
        }
        if (isset($this->filters['end_date'])) {
            $query->whereDate('planned_end_at', '<=', $this->filters['end_date']);
        }

        // Filter by overdue
        if (isset($this->filters['overdue']) && $this->filters['overdue']) {
            $today = Carbon::today();
            $query->where('due_at', '<', $today)
                ->whereNotIn('status', ['done', 'canceled']);
        }

        return $query->orderBy('project_id')
            ->orderBy('phase_id')
            ->orderBy('order_in_phase');
    }

    /**
     * Format status value for CSV.
     */
    protected function formatStatus(string $status): string
    {
        return match ($status) {
            'backlog' => 'Backlog',
            'in_progress' => 'Em Progresso',
            'in_review' => 'Em Revisão',
            'done' => 'Concluída',
            'canceled' => 'Cancelada',
            default => $status,
        };
    }

    /**
     * Format priority value for CSV.
     */
    protected function formatPriority(string $priority): string
    {
        return match ($priority) {
            'low' => 'Baixa',
            'medium' => 'Média',
            'high' => 'Alta',
            'urgent' => 'Urgente',
            default => $priority,
        };
    }
}

