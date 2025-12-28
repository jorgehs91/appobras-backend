<?php

namespace App\Http\Requests\TaskDependency;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskDependencyRequest extends FormRequest
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
        return [
            'task_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:tasks,id',
            ],
            'depends_on_task_id' => [
                'sometimes',
                'required',
                'integer',
                'different:task_id',
                'exists:tasks,id',
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
            'task_id.exists' => 'A tarefa selecionada não existe ou não pertence ao projeto.',
            'depends_on_task_id.different' => 'Uma tarefa não pode depender de si mesma.',
            'depends_on_task_id.exists' => 'A tarefa dependente não existe ou não pertence ao projeto.',
        ];
    }
}
