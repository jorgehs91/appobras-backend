<?php

namespace App\Jobs;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;

class PaymentsCsvExportJob extends BaseCsvExportJob
{
    protected function getReportType(): string
    {
        return 'payments';
    }

    protected function getHeaders(): array
    {
        return [
            'ID',
            'Obra',
            'Obra ID',
            'Prestador',
            'Referência/Contrato',
            'Tipo',
            'Vencimento',
            'Valor',
            'Status',
            'Pago em',
            'Criado em',
        ];
    }

    protected function processDataInChunks(callable $callback, int $chunkSize): void
    {
        $this->buildQuery()->chunk($chunkSize, $callback);
    }

    protected function formatRow($payment): array
    {
        $payable = $payment->payable;
        $contractor = $payable?->contractor ?? null;
        $project = $payable?->project ?? null;

        return [
            $payment->id,
            $project?->name ?? '',
            $project?->id ?? '',
            $contractor?->name ?? '',
            $payable?->id ?? '',
            class_basename($payment->payable_type),
            $payment->due_date?->format('d/m/Y') ?? '',
            number_format($payment->amount, 2, ',', '.'),
            $this->formatStatus($payment->status->value),
            $payment->paid_at?->format('d/m/Y H:i') ?? '',
            $payment->created_at->format('d/m/Y H:i'),
        ];
    }

    protected function buildQuery(): Builder
    {
        $query = Payment::query()
            ->with(['payable.contractor', 'payable.project']);

        if (isset($this->filters['project_id'])) {
            $query->whereHasMorph('payable', [\App\Models\Contract::class, \App\Models\WorkOrder::class], function ($q) {
                $q->where('project_id', $this->filters['project_id']);
            });
        }

        if (isset($this->filters['contractor_id'])) {
            $query->whereHasMorph('payable', [\App\Models\Contract::class, \App\Models\WorkOrder::class], function ($q) {
                $q->where('contractor_id', $this->filters['contractor_id']);
            });
        }

        if (isset($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['start_date'])) {
            $query->whereDate('due_date', '>=', $this->filters['start_date']);
        }
        if (isset($this->filters['end_date'])) {
            $query->whereDate('due_date', '<=', $this->filters['end_date']);
        }

        return $query->orderBy('due_date');
    }

    protected function formatStatus(string $status): string
    {
        return match ($status) {
            'pending' => 'Pendente',
            'paid' => 'Pago',
            'overdue' => 'Vencido',
            default => $status,
        };
    }
}

