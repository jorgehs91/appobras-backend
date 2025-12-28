<?php

namespace App\Http\Requests\TaskDependency;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskDependencyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $projectId = $this->route('project')?->id ?? $this->input('project_id');

        return [
            'task_id' => [
                'required',
                'integer',
                Rule::exists('tasks', 'id')->where('project_id', $projectId),
            ],
            'depends_on_task_id' => [
                'required',
                'integer',
                'different:task_id',
                Rule::exists('tasks', 'id')->where('project_id', $projectId),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'task_id.required' => 'A tarefa é obrigatória.',
            'task_id.exists' => 'A tarefa selecionada não existe ou não pertence ao projeto.',
            'depends_on_task_id.required' => 'A tarefa dependente é obrigatória.',
            'depends_on_task_id.different' => 'Uma tarefa não pode depender de si mesma.',
            'depends_on_task_id.exists' => 'A tarefa dependente não existe ou não pertence ao projeto.',
        ];
    }
}
