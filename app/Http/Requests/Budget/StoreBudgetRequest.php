<?php

namespace App\Http\Requests\Budget;

use Illuminate\Foundation\Http\FormRequest;

class StoreBudgetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'total_planned' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
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
            'total_planned.required' => 'O valor total planejado é obrigatório.',
            'total_planned.numeric' => 'O valor total planejado deve ser um número.',
            'total_planned.min' => 'O valor total planejado deve ser maior ou igual a zero.',
            'total_planned.max' => 'O valor total planejado excede o limite máximo permitido.',
        ];
    }
}