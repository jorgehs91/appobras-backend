<?php

namespace App\Jobs;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Builder;

class AuditLogsCsvExportJob extends BaseCsvExportJob
{
    protected function getReportType(): string
    {
        return 'audit-logs';
    }

    protected function getHeaders(): array
    {
        return [
            'ID',
            'Usuário',
            'Ação',
            'Modelo',
            'Modelo ID',
            'IP',
            'Data',
        ];
    }

    protected function processDataInChunks(callable $callback, int $chunkSize): void
    {
        $this->buildQuery()->chunk($chunkSize, $callback);
    }

    protected function formatRow($log): array
    {
        return [
            $log->id,
            $log->user?->name ?? '',
            $log->event,
            class_basename($log->auditable_type),
            $log->auditable_id ?? '',
            $log->ip ?? '',
            $log->created_at->format('d/m/Y H:i:s'),
        ];
    }

    protected function buildQuery(): Builder
    {
        $query = AuditLog::query()
            ->with('user');

        if (isset($this->filters['user_id'])) {
            $query->where('user_id', $this->filters['user_id']);
        }

        if (isset($this->filters['action'])) {
            $query->where('event', $this->filters['action']);
        }

        if (isset($this->filters['project_id'])) {
            $query->where('auditable_type', \App\Models\Project::class)
                ->where('auditable_id', $this->filters['project_id']);
        }

        if (isset($this->filters['start_date'])) {
            $query->whereDate('created_at', '>=', $this->filters['start_date']);
        }
        if (isset($this->filters['end_date'])) {
            $query->whereDate('created_at', '<=', $this->filters['end_date']);
        }

        return $query->orderBy('created_at', 'desc');
    }
}

