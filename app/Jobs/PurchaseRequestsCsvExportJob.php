<?php

namespace App\Jobs;

use App\Models\PurchaseRequest;
use Illuminate\Database\Eloquent\Builder;

/**
 * Export purchase requests to CSV format.
 * 
 * Filters: project, status, period, requester
 * Fields: id, obra, solicitante, status, total_itens, valor_estimado, aprovado_por, aprovado_em
 */
class PurchaseRequestsCsvExportJob extends BaseCsvExportJob
{
    protected function getReportType(): string
    {
        return 'purchase-requests';
    }

    protected function getHeaders(): array
    {
        return [
            'ID',
            'Obra',
            'Obra ID',
            'Fornecedor',
            'Solicitante',
            'Status',
            'Total Itens',
            'Valor Estimado',
            'Aprovado por',
            'Aprovado em',
            'Criado em',
            'Atualizado em',
        ];
    }

    protected function processDataInChunks(callable $callback, int $chunkSize): void
    {
        $this->buildQuery()->chunk($chunkSize, $callback);
    }

    protected function formatRow($pr): array
    {
        return [
            $pr->id,
            $pr->project?->name ?? '',
            $pr->project_id,
            $pr->supplier?->name ?? '',
            $pr->creator?->name ?? '',
            $this->formatStatus($pr->status->value),
            $pr->items()->count(),
            number_format($pr->total, 2, ',', '.'),
            $pr->purchaseOrder?->approvedBy?->name ?? '',
            $pr->purchaseOrder?->created_at?->format('d/m/Y H:i') ?? '',
            $pr->created_at->format('d/m/Y H:i'),
            $pr->updated_at->format('d/m/Y H:i'),
        ];
    }

    protected function buildQuery(): Builder
    {
        $query = PurchaseRequest::query()
            ->with(['project', 'supplier', 'creator', 'items', 'purchaseOrder.approvedBy'])
            ->whereHas('project', function ($q) {
                $q->where('company_id', $this->companyId);
            });

        if ($this->projectId) {
            $query->where('project_id', $this->projectId);
        } elseif (isset($this->filters['project_id'])) {
            $query->where('project_id', $this->filters['project_id']);
        }

        if (isset($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['start_date'])) {
            $query->whereDate('created_at', '>=', $this->filters['start_date']);
        }
        if (isset($this->filters['end_date'])) {
            $query->whereDate('created_at', '<=', $this->filters['end_date']);
        }

        if (isset($this->filters['requester_id'])) {
            $query->where('created_by', $this->filters['requester_id']);
        }

        return $query->orderBy('created_at', 'desc');
    }

    protected function formatStatus(string $status): string
    {
        return match ($status) {
            'draft' => 'Rascunho',
            'submitted' => 'Submetida',
            'approved' => 'Aprovada',
            'rejected' => 'Rejeitada',
            default => $status,
        };
    }
}

