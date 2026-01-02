<?php

namespace App\Jobs;

use App\Models\License;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class LicensesCsvExportJob extends BaseCsvExportJob
{
    protected function getReportType(): string
    {
        return 'licenses';
    }

    protected function getHeaders(): array
    {
        return [
            'ID',
            'Obra',
            'Obra ID',
            'Arquivo',
            'Data Vencimento',
            'Dias até Vencimento',
            'Status',
            'Criado em',
        ];
    }

    protected function processDataInChunks(callable $callback, int $chunkSize): void
    {
        $this->buildQuery()->chunk($chunkSize, $callback);
    }

    protected function formatRow($license): array
    {
        $daysUntilExpiration = $license->expiry_date 
            ? Carbon::today()->diffInDays($license->expiry_date, false)
            : null;

        return [
            $license->id,
            $license->project?->name ?? '',
            $license->project_id ?? '',
            $license->file?->name ?? '',
            $license->expiry_date?->format('d/m/Y') ?? '',
            $daysUntilExpiration !== null ? (string) $daysUntilExpiration : '',
            $this->formatStatus($license->status ?? 'active'),
            $license->created_at->format('d/m/Y H:i'),
        ];
    }

    protected function buildQuery(): Builder
    {
        $query = License::query()
            ->with(['project', 'file'])
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

        if (isset($this->filters['expiring_days'])) {
            $cutoffDate = Carbon::today()->addDays($this->filters['expiring_days']);
            $query->where('expiry_date', '<=', $cutoffDate)
                ->where('expiry_date', '>=', Carbon::today());
        }

        return $query->orderBy('expiry_date');
    }

    protected function formatStatus(string $status): string
    {
        return match ($status) {
            'active' => 'Ativa',
            'expired' => 'Expirada',
            'pending_renewal' => 'Renovação Pendente',
            default => $status,
        };
    }
}

