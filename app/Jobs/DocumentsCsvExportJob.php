<?php

namespace App\Jobs;

use App\Models\File;
use Illuminate\Database\Eloquent\Builder;

class DocumentsCsvExportJob extends BaseCsvExportJob
{
    protected function getReportType(): string
    {
        return 'documents';
    }

    protected function getHeaders(): array
    {
        return [
            'ID',
            'Obra',
            'Obra ID',
            'Categoria',
            'Nome',
            'Tamanho (bytes)',
            'Tipo MIME',
            'Upload em',
            'Upload por',
        ];
    }

    protected function processDataInChunks(callable $callback, int $chunkSize): void
    {
        $this->buildQuery()->chunk($chunkSize, $callback);
    }

    protected function formatRow($file): array
    {
        return [
            $file->id,
            $file->project?->name ?? '',
            $file->project_id ?? '',
            $file->category ?? '',
            $file->name,
            $file->size ?? 0,
            $file->mime_type ?? '',
            $file->created_at->format('d/m/Y H:i'),
            $file->uploader?->name ?? '',
        ];
    }

    protected function buildQuery(): Builder
    {
        $query = File::query()
            ->with(['project', 'uploader'])
            ->where('company_id', $this->companyId)
            ->where('category', 'document');

        if ($this->projectId) {
            $query->where('project_id', $this->projectId);
        } elseif (isset($this->filters['project_id'])) {
            $query->where('project_id', $this->filters['project_id']);
        }

        if (isset($this->filters['category'])) {
            $query->where('category', $this->filters['category']);
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

