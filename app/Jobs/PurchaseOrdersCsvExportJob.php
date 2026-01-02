<?php

namespace App\Jobs;

use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Builder;

/**
 * Export purchase orders to CSV format.
 * 
 * Filters: project, supplier, status, period
 * Fields: id, obra, fornecedor, status, total, origin_pr_id, criado_em
 */
class PurchaseOrdersCsvExportJob extends BaseCsvExportJob
{
    protected function getReportType(): string
    {
        return 'purchase-orders';
    }

    protected function getHeaders(): array
    {
        return [
            'ID',
            'Número PO',
            'Obra',
            'Obra ID',
            'Fornecedor',
            'Status',
            'Total',
            'PR Origem ID',
            'Criado em',
            'Atualizado em',
        ];
    }

    protected function processDataInChunks(callable $callback, int $chunkSize): void
    {
        $this->buildQuery()->chunk($chunkSize, $callback);
    }

    protected function formatRow($po): array
    {
        return [
            $po->id,
            $po->po_number ?? '',
            $po->purchaseRequest?->project?->name ?? '',
            $po->purchaseRequest?->project_id ?? '',
            $po->purchaseRequest?->supplier?->name ?? '',
            $this->formatStatus($po->status->value),
            number_format($po->total, 2, ',', '.'),
            $po->purchase_request_id ?? '',
            $po->created_at->format('d/m/Y H:i'),
            $po->updated_at->format('d/m/Y H:i'),
        ];
    }

    protected function buildQuery(): Builder
    {
        $query = PurchaseOrder::query()
            ->with(['purchaseRequest.project', 'purchaseRequest.supplier'])
            ->whereHas('purchaseRequest.project', function ($q) {
                $q->where('company_id', $this->companyId);
            });

        if ($this->projectId) {
            $query->whereHas('purchaseRequest', function ($q) {
                $q->where('project_id', $this->projectId);
            });
        } elseif (isset($this->filters['project_id'])) {
            $query->whereHas('purchaseRequest', function ($q) {
                $q->where('project_id', $this->filters['project_id']);
            });
        }

        if (isset($this->filters['supplier_id'])) {
            $query->whereHas('purchaseRequest', function ($q) {
                $q->where('supplier_id', $this->filters['supplier_id']);
            });
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

        return $query->orderBy('created_at', 'desc');
    }

    protected function formatStatus(string $status): string
    {
        return match ($status) {
            'pending' => 'Pendente',
            'approved' => 'Aprovado',
            'rejected' => 'Rejeitado',
            'completed' => 'Concluído',
            default => $status,
        };
    }
}

