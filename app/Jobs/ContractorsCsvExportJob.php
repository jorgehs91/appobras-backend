<?php

namespace App\Jobs;

use App\Models\Contractor;
use Illuminate\Database\Eloquent\Builder;

class ContractorsCsvExportJob extends BaseCsvExportJob
{
    protected function getReportType(): string
    {
        return 'contractors';
    }

    protected function getHeaders(): array
    {
        return [
            'ID',
            'Nome',
            'Contato',
            'Especialidades',
            'Total Contratos',
            'Total Tarefas',
            'Criado em',
            'Atualizado em',
        ];
    }

    protected function processDataInChunks(callable $callback, int $chunkSize): void
    {
        $this->buildQuery()->chunk($chunkSize, $callback);
    }

    protected function formatRow($contractor): array
    {
        return [
            $contractor->id,
            $contractor->name,
            $contractor->contact ?? '',
            $contractor->specialties ?? '',
            $contractor->contracts()->count(),
            $contractor->tasks()->count(),
            $contractor->created_at->format('d/m/Y H:i'),
            $contractor->updated_at->format('d/m/Y H:i'),
        ];
    }

    protected function buildQuery(): Builder
    {
        $query = Contractor::query()
            ->withCount(['contracts', 'tasks'])
            ->where('company_id', $this->companyId);

        if (isset($this->filters['project_id'])) {
            $query->whereHas('contracts', function ($q) {
                $q->where('project_id', $this->filters['project_id']);
            });
        }

        return $query->orderBy('name');
    }
}

