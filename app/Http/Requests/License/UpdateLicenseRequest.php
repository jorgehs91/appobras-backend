<?php

namespace App\Http\Requests\License;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLicenseRequest extends FormRequest
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
            'file_id' => ['sometimes', 'integer', 'exists:files,id'],
            'project_id' => ['sometimes', 'integer', 'exists:projects,id'],
            'expiry_date' => ['sometimes', 'date', 'after_or_equal:today'],
            'status' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:65535'],
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
            'file_id.exists' => 'O arquivo selecionado não existe.',
            'project_id.exists' => 'O projeto selecionado não existe.',
            'expiry_date.date' => 'A data de vencimento deve ser uma data válida.',
            'expiry_date.after_or_equal' => 'A data de vencimento deve ser igual ou posterior à data de hoje.',
            'status.max' => 'O status não pode ter mais de 255 caracteres.',
            'notes.max' => 'As observações não podem ter mais de 65535 caracteres.',
        ];
    }
}

