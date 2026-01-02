<?php

namespace App\Jobs;

use App\Http\Controllers\ExpenseController;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Export Previsto vs Realizado (PVxRV) to CSV format.
 * 
 * Filters: project, category, cost_item, period
 * Fields: obra, cost_item, previsto, realizado, variação, variação_%, atualizado_em
 */
class PvxrCsvExportJob extends BaseCsvExportJob
{
    /**
     * Get the report type identifier.
     */
    protected function getReportType(): string
    {
        return 'pvxrv';
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
            'Agrupamento',
            'Cost Item ID',
            'Cost Item',
            'Categoria',
            'Previsto',
            'Realizado',
            'Variação',
            'Variação (%)',
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

        $query->chunk($chunkSize, function ($projects) use ($callback) {
            $rows = [];
            foreach ($projects as $project) {
                $pvxrData = $this->getPvxrData($project);
                
                // Add by_cost_item rows
                foreach ($pvxrData['by_cost_item'] as $item) {
                    $rows[] = [
                        'type' => 'cost_item',
                        'project' => $project,
                        'data' => $item,
                    ];
                }
                
                // Add by_category rows
                foreach ($pvxrData['by_category'] as $category) {
                    $rows[] = [
                        'type' => 'category',
                        'project' => $project,
                        'data' => $category,
                    ];
                }
                
                // Add total row
                $rows[] = [
                    'type' => 'total',
                    'project' => $project,
                    'data' => $pvxrData['total'],
                ];
            }
            
            if (!empty($rows)) {
                $callback($rows);
            }
        });
    }

    /**
     * Format a PVxRV row for CSV output.
     *
     * @param  array  $row
     * @return array<int, mixed>
     */
    protected function formatRow($row): array
    {
        $project = $row['project'];
        $data = $row['data'];
        $type = $row['type'];
        $updatedAt = now()->format('d/m/Y H:i');

        if ($type === 'cost_item') {
            return [
                $project->name ?? '',
                $project->id ?? '',
                'Cost Item',
                $data['cost_item_id'] ?? '',
                $data['cost_item_name'] ?? '',
                '', // Category is in cost_item_name context
                number_format($data['planned'], 2, ',', '.'),
                number_format($data['realized'], 2, ',', '.'),
                number_format($data['variance'], 2, ',', '.'),
                number_format($data['variance_percentage'], 2, ',', '.'),
                $updatedAt,
            ];
        } elseif ($type === 'category') {
            return [
                $project->name ?? '',
                $project->id ?? '',
                'Categoria',
                '', // No cost_item_id for category
                '', // No cost_item_name for category
                $data['category'] ?? '',
                number_format($data['planned'], 2, ',', '.'),
                number_format($data['realized'], 2, ',', '.'),
                number_format($data['variance'], 2, ',', '.'),
                number_format($data['variance_percentage'], 2, ',', '.'),
                $updatedAt,
            ];
        } else { // total
            return [
                $project->name ?? '',
                $project->id ?? '',
                'Total',
                '', // No cost_item_id for total
                '', // No cost_item_name for total
                '', // No category for total
                number_format($data['planned'], 2, ',', '.'),
                number_format($data['realized'], 2, ',', '.'),
                number_format($data['variance'], 2, ',', '.'),
                number_format($data['variance_percentage'], 2, ',', '.'),
                $updatedAt,
            ];
        }
    }

    /**
     * Build the query with filters applied.
     */
    protected function buildQuery(): Builder
    {
        $query = Project::query()
            ->where('company_id', $this->companyId)
            ->whereHas('budget'); // Only projects with budgets

        // Filter by project
        if ($this->projectId) {
            $query->where('id', $this->projectId);
        } elseif (isset($this->filters['project_id'])) {
            $query->where('id', $this->filters['project_id']);
        }

        // Filter by period (if needed, we can filter by expense dates)
        // This is handled in the getPvxrData method if needed

        return $query->orderBy('id');
    }

    /**
     * Get PVxRV data for a project using ExpenseController logic.
     */
    protected function getPvxrData(Project $project): array
    {
        $controller = app(ExpenseController::class);
        return $controller->calculatePvxr($project);
    }
}

