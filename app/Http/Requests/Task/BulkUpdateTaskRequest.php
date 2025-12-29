<?php

namespace App\Http\Requests\Task;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkUpdateTaskRequest extends FormRequest
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
        $projectId = $this->route('project')?->id;

        return [
            'tasks' => ['required', 'array', 'min:1'],
            'tasks.*.id' => [
                'required',
                'integer',
                Rule::exists('tasks', 'id')->where('project_id', $projectId),
            ],
            'tasks.*.position' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'tasks.*.status' => ['sometimes', 'nullable', 'in:backlog,in_progress,in_review,done,canceled'],
            'tasks.*.phase_id' => ['sometimes', 'nullable', 'integer', 'exists:phases,id'],
            'tasks.*.priority' => ['sometimes', 'nullable', 'in:low,medium,high,urgent'],
            'tasks.*.assignee_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'tasks.*.contractor_id' => ['sometimes', 'nullable', 'integer', 'exists:contractors,id'],
            'tasks.*.is_blocked' => ['sometimes', 'nullable', 'boolean'],
            'tasks.*.blocked_reason' => ['sometimes', 'nullable', 'string', 'max:255'],
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
            'tasks.required' => 'O array de tarefas é obrigatório.',
            'tasks.array' => 'As tarefas devem ser enviadas em um array.',
            'tasks.min' => 'É necessário enviar pelo menos uma tarefa.',
            'tasks.*.id.required' => 'O ID da tarefa é obrigatório.',
            'tasks.*.id.exists' => 'A tarefa selecionada não existe ou não pertence ao projeto.',
            'tasks.*.position.numeric' => 'A posição deve ser um número.',
            'tasks.*.position.min' => 'A posição deve ser maior ou igual a zero.',
            'tasks.*.status.in' => 'O status deve ser: backlog, in_progress, in_review, done ou canceled.',
            'tasks.*.phase_id.exists' => 'A fase selecionada não existe.',
            'tasks.*.priority.in' => 'A prioridade deve ser: low, medium, high ou urgent.',
            'tasks.*.assignee_id.exists' => 'O usuário atribuído não existe.',
            'tasks.*.contractor_id.exists' => 'O empreiteiro não existe.',
        ];
    }
}
