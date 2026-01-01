<?php

namespace App\Http\Requests\TaskComment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskCommentRequest extends FormRequest
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
            'body' => ['sometimes', 'required', 'string', 'max:10000'],
            'reactions' => ['sometimes', 'array'],
            'reactions.*' => ['integer', 'min:0'],
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
            'body.required' => 'O conteúdo do comentário é obrigatório.',
            'body.string' => 'O conteúdo do comentário deve ser um texto válido.',
            'body.max' => 'O conteúdo do comentário não pode ter mais de 10000 caracteres.',
            'reactions.array' => 'As reactions devem ser um objeto JSON válido.',
            'reactions.*.integer' => 'Cada reaction deve ser um número inteiro.',
            'reactions.*.min' => 'Cada reaction deve ser maior ou igual a zero.',
        ];
    }
}
