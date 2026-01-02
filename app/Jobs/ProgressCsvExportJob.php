<?php

namespace App\Jobs;

use App\Models\Phase;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Export project progress to CSV format.
 * 
 * Filters: project, period (start_date, end_date)
 * Fields: obra, fase, total_tarefas, progresso_fase(%), progresso_obra(%), referência_data
 */
class ProgressCsvExportJob extends BaseCsvExportJob
{
    /**
     * Get the report type identifier.
     */
    protected function getReportType(): string
    {
        return 'progress';
    }

    /**
     * Get CSV headers in Portuguese (pt-BR).
     *
     * @return array<int, string>
     */
    protected function getHeaders(): array
    {
        return [
            'Obra',
            'Obra ID',
            'Fase',
            'Fase ID',
            'Status Fase',
            'Total Tarefas',
            'Tarefas Backlog',
            'Tarefas Em Progresso',
            'Tarefas Em Revisão',
            'Tarefas Concluídas',
            'Tarefas Canceladas',
            'Progresso Fase (%)',
            'Progresso Obra (%)',
            'Data Início Planejada',
            'Data Fim Planejada',
            'Data Início Real',
            'Data Fim Real',
            'Referência Data',
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

        $query->chunk($chunkSize, function ($phases) use ($callback) {
            $rows = [];
            foreach ($phases as $phase) {
                $rows[] = $phase;
            }
            $callback($rows);
        });
    }

    /**
     * Format a phase row for CSV output.
     *
     * @param  Phase  $phase
     * @return array<int, mixed>
     */
    protected function formatRow($phase): array
    {
        $project = $phase->project;
        $tasksCounts = $phase->tasks_counts;
        $referenceDate = now()->format('d/m/Y');

        return [
            $project->name ?? '',
            $project->id ?? '',
            $phase->name,
            $phase->id,
            $this->formatPhaseStatus($phase->status->value),
            $tasksCounts['total'],
            $tasksCounts['backlog'],
            $tasksCounts['in_progress'],
            $tasksCounts['in_review'],
            $tasksCounts['done'],
            $tasksCounts['canceled'],
            $phase->progress_percent,
            $project->progress_percent ?? 0,
            $phase->planned_start_at?->format('d/m/Y') ?? '',
            $phase->planned_end_at?->format('d/m/Y') ?? '',
            $phase->actual_start_at?->format('d/m/Y') ?? '',
            $phase->actual_end_at?->format('d/m/Y') ?? '',
            $referenceDate,
        ];
    }

    /**
     * Build the query with filters applied.
     */
    protected function buildQuery(): Builder
    {
        $query = Phase::query()
            ->with(['project', 'tasks'])
            ->where('company_id', $this->companyId);

        // Filter by project
        if ($this->projectId) {
            $query->where('project_id', $this->projectId);
        } elseif (isset($this->filters['project_id'])) {
            $query->where('project_id', $this->filters['project_id']);
        }

        // Filter by period (start date)
        if (isset($this->filters['start_date'])) {
            $query->where(function ($q) {
                $q->whereDate('planned_start_at', '>=', $this->filters['start_date'])
                    ->orWhereDate('actual_start_at', '>=', $this->filters['start_date']);
            });
        }

        // Filter by period (end date)
        if (isset($this->filters['end_date'])) {
            $query->where(function ($q) {
                $q->whereDate('planned_end_at', '<=', $this->filters['end_date'])
                    ->orWhereDate('actual_end_at', '<=', $this->filters['end_date']);
            });
        }

        // Only include active phases by default, unless filter specifies otherwise
        if (!isset($this->filters['include_archived']) || !$this->filters['include_archived']) {
            $query->where('status', 'active');
        }

        return $query->orderBy('project_id')
            ->orderBy('sequence');
    }

    /**
     * Format phase status value for CSV.
     */
    protected function formatPhaseStatus(string $status): string
    {
        return match ($status) {
            'draft' => 'Rascunho',
            'active' => 'Ativa',
            'archived' => 'Arquivada',
            default => $status,
        };
    }
}

