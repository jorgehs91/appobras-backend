<?php

namespace App\Http\Requests\CostItem;

use Illuminate\Foundation\Http\FormRequest;

class StoreCostItemRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'planned_amount' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'unit' => ['nullable', 'string', 'max:255'],
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
            'name.required' => 'O nome do item de custo é obrigatório.',
            'name.max' => 'O nome do item de custo não pode ter mais de 255 caracteres.',
            'category.required' => 'A categoria é obrigatória.',
            'category.max' => 'A categoria não pode ter mais de 255 caracteres.',
            'planned_amount.required' => 'O valor planejado é obrigatório.',
            'planned_amount.numeric' => 'O valor planejado deve ser um número.',
            'planned_amount.min' => 'O valor planejado deve ser maior ou igual a zero.',
            'planned_amount.max' => 'O valor planejado excede o limite máximo permitido.',
            'unit.max' => 'A unidade não pode ter mais de 255 caracteres.',
        ];
    }
}