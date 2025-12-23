<?php

namespace App\Http\Requests\Project;

use App\Enums\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Todos usuários da empresa podem criar projetos
        return true;
    }

    /**
     * @return array<string, array<int, string|\\Illuminate\\Validation\\Rules\\Unique>>
     */
    public function rules(): array
    {
        $companyId = (int) $this->header('X-Company-Id');

        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                // unicidade por empresa
                'unique:projects,name,NULL,id,company_id,'.$companyId,
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', 'string', 'in:'.implode(',', array_column(ProjectStatus::cases(), 'value'))],
            'archived_at' => ['nullable', 'date'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'actual_start_date' => ['nullable', 'date'],
            'actual_end_date' => ['nullable', 'date', 'after_or_equal:actual_start_date'],
            'planned_budget_amount' => ['nullable', 'numeric', 'min:0'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }
}


