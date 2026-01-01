<?php

namespace App\Http\Requests\WorkOrder;

use App\Enums\WorkOrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkOrderRequest extends FormRequest
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
            'contract_id' => ['required', 'integer', 'exists:contracts,id'],
            'description' => ['required', 'string', 'max:2000'],
            'value' => ['required', 'numeric', 'min:0.01', 'max:9999999999999.99'],
            'due_date' => ['nullable', 'date'],
            'status' => ['required', 'string', Rule::enum(WorkOrderStatus::class)],
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
            'contract_id.required' => 'O contrato é obrigatório.',
            'contract_id.exists' => 'O contrato selecionado não existe.',
            'description.required' => 'A descrição da ordem de serviço é obrigatória.',
            'description.max' => 'A descrição não pode ter mais de 2000 caracteres.',
            'value.required' => 'O valor da ordem de serviço é obrigatório.',
            'value.numeric' => 'O valor da ordem de serviço deve ser um número.',
            'value.min' => 'O valor da ordem de serviço deve ser maior que zero.',
            'value.max' => 'O valor da ordem de serviço excede o limite máximo permitido.',
            'due_date.date' => 'A data de vencimento deve ser uma data válida.',
            'status.required' => 'O status da ordem de serviço é obrigatório.',
            'status.enum' => 'O status da ordem de serviço é inválido.',
        ];
    }
}

