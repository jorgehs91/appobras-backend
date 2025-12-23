<?php

namespace App\Http\Requests\Phase;

use Illuminate\Foundation\Http\FormRequest;

class StorePhaseRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'in:draft,active,archived'],
            'sequence' => ['nullable', 'integer', 'min:0'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'planned_start_at' => ['nullable', 'date'],
            'planned_end_at' => ['nullable', 'date', 'after_or_equal:planned_start_at'],
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
            'name.required' => 'O nome da fase é obrigatório.',
            'name.max' => 'O nome da fase não pode ter mais de 255 caracteres.',
            'status.in' => 'O status deve ser: draft, active ou archived.',
            'sequence.min' => 'A sequência deve ser maior ou igual a 0.',
            'color.regex' => 'A cor deve estar no formato hexadecimal (#RRGGBB).',
            'planned_end_at.after_or_equal' => 'A data de término planejada deve ser igual ou posterior à data de início.',
        ];
    }
}

