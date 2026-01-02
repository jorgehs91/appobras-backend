<?php

namespace App\Jobs;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Builder;

/**
 * Export expenses to CSV format.
 * 
 * Filters: project, supplier, period, category
 * Fields: id, obra, data, fornecedor, categoria, descrição, valor, criado_por
 */
class ExpensesCsvExportJob extends BaseCsvExportJob
{
    /**
     * Get the report type identifier.
     */
    protected function getReportType(): string
    {
        return 'expenses';
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
            'Obra ID',
            'Data',
            'Fornecedor',
            'Categoria',
            'Cost Item',
            'Descrição',
            'Valor',
            'Status',
            'Criado por',
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

        $query->chunk($chunkSize, function ($expenses) use ($callback) {
            $callback($expenses);
        });
    }

    /**
     * Format an expense row for CSV output.
     *
     * @param  Expense  $expense
     * @return array<int, mixed>
     */
    protected function formatRow($expense): array
    {
        return [
            $expense->id,
            $expense->project?->name ?? '',
            $expense->project_id,
            $expense->date?->format('d/m/Y') ?? '',
            '', // Supplier não está diretamente relacionado a Expense
            $expense->costItem?->category ?? '',
            $expense->costItem?->name ?? '',
            $expense->description ?? '',
            number_format($expense->amount, 2, ',', '.'),
            $this->formatStatus($expense->status->value),
            $expense->creator?->name ?? '',
            $expense->created_at->format('d/m/Y H:i'),
            $expense->updated_at->format('d/m/Y H:i'),
        ];
    }

    /**
     * Build the query with filters applied.
     */
    protected function buildQuery(): Builder
    {
        $query = Expense::query()
            ->with(['project', 'costItem', 'creator'])
            ->whereHas('project', function ($q) {
                $q->where('company_id', $this->companyId);
            });

        // Filter by project
        if ($this->projectId) {
            $query->where('project_id', $this->projectId);
        } elseif (isset($this->filters['project_id'])) {
            $query->where('project_id', $this->filters['project_id']);
        }

        // Filter by supplier - not directly available in Expense model
        // This filter is kept for API compatibility but won't filter anything
        // TODO: Add supplier relationship to Expense if needed

        // Filter by category
        if (isset($this->filters['category'])) {
            $query->whereHas('costItem', function ($q) {
                $q->where('category', $this->filters['category']);
            });
        }

        // Filter by period
        if (isset($this->filters['start_date'])) {
            $query->whereDate('date', '>=', $this->filters['start_date']);
        }
        if (isset($this->filters['end_date'])) {
            $query->whereDate('date', '<=', $this->filters['end_date']);
        }

        // Filter by status
        if (isset($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('date', 'desc')
            ->orderBy('id');
    }

    /**
     * Format status value for CSV.
     */
    protected function formatStatus(string $status): string
    {
        return match ($status) {
            'draft' => 'Rascunho',
            'approved' => 'Aprovada',
            default => $status,
        };
    }
}

