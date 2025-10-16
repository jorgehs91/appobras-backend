<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string|\Illuminate\Validation\Rules\Exists|\Illuminate\Validation\Rules\In|\Illuminate\Validation\Rules\Prohibited>>
     */
    public function rules(): array
    {
        $companyId = (int) $this->header('X-Company-Id');

        return [
            'company_id' => ['prohibited'],
            'name' => ['sometimes', 'string', 'min:2', 'max:255', 'unique:projects,name,'.$this->route('project')->id.',id,company_id,'.$companyId],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'status' => ['sometimes', 'string', Rule::in(['planned', 'in_progress', 'paused', 'completed', 'cancelled'])],
            'archived_at' => ['sometimes', 'nullable', 'date'],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date'],
            'actual_start_date' => ['sometimes', 'nullable', 'date'],
            'actual_end_date' => ['sometimes', 'nullable', 'date'],
            'planned_budget_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'manager_user_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:users,id',
                Rule::exists('company_user', 'user_id')->where('company_id', $companyId),
            ],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    public function after(): array
    {
        return [function (): void {
            /** @var \App\Models\Project $project */
            $project = $this->route('project');

            $start = $this->has('start_date') ? $this->dateOrNull('start_date') : ($project->start_date ? Carbon::parse((string) $project->start_date) : null);
            $end = $this->has('end_date') ? $this->dateOrNull('end_date') : ($project->end_date ? Carbon::parse((string) $project->end_date) : null);
            if ($start && $end && $end->lt($start)) {
                $this->validator->errors()->add('end_date', 'The end date must be a date after or equal to start date.');
            }

            $astart = $this->has('actual_start_date') ? $this->dateOrNull('actual_start_date') : ($project->actual_start_date ? Carbon::parse((string) $project->actual_start_date) : null);
            $aend = $this->has('actual_end_date') ? $this->dateOrNull('actual_end_date') : ($project->actual_end_date ? Carbon::parse((string) $project->actual_end_date) : null);
            if ($astart && $aend && $aend->lt($astart)) {
                $this->validator->errors()->add('actual_end_date', 'The actual end date must be a date after or equal to actual start date.');
            }
        }];
    }

    private function dateOrNull(string $key): ?Carbon
    {
        $value = $this->input($key);
        return $value ? Carbon::parse((string) $value) : null;
    }
}


