<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by policy
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phase_id' => ['sometimes', 'exists:phases,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:backlog,in_progress,in_review,done,canceled'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
            'order_in_phase' => ['sometimes', 'integer', 'min:0'],
            'assignee_id' => ['nullable', 'exists:users,id'],
            'contractor_id' => ['nullable', 'exists:contractors,id'],
            'is_blocked' => ['sometimes', 'boolean'],
            'blocked_reason' => ['nullable', 'string', 'max:255'],
            'planned_start_at' => ['nullable', 'date'],
            'planned_end_at' => ['nullable', 'date', 'after_or_equal:planned_start_at'],
            'due_at' => ['nullable', 'date'],
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
            'phase_id.exists' => 'A fase selecionada não existe.',
            'title.max' => 'O título não pode ter mais de 255 caracteres.',
            'status.in' => 'O status deve ser: backlog, in_progress, in_review, done ou canceled.',
            'priority.in' => 'A prioridade deve ser: low, medium, high ou urgent.',
            'assignee_id.exists' => 'O usuário atribuído não existe.',
            'contractor_id.exists' => 'O empreiteiro não existe.',
            'planned_end_at.after_or_equal' => 'A data de término planejada deve ser igual ou posterior à data de início.',
        ];
    }
}

