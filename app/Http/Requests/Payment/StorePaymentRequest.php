<?php

namespace App\Http\Requests\Payment;

use App\Enums\PaymentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
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
            'payable_type' => ['required', 'string', Rule::in(['App\\Models\\Contract', 'App\\Models\\WorkOrder'])],
            'payable_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999999.99'],
            'due_date' => ['required', 'date'],
            'status' => ['required', 'string', Rule::enum(PaymentStatus::class)],
            'paid_at' => ['nullable', 'date'],
            'payment_proof_path' => ['nullable', 'string', 'max:500'],
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
            'payable_type.required' => 'O tipo de pagamento é obrigatório.',
            'payable_type.in' => 'O tipo de pagamento deve ser Contract ou WorkOrder.',
            'payable_id.required' => 'O ID do pagável é obrigatório.',
            'payable_id.integer' => 'O ID do pagável deve ser um número inteiro.',
            'amount.required' => 'O valor do pagamento é obrigatório.',
            'amount.numeric' => 'O valor do pagamento deve ser um número.',
            'amount.min' => 'O valor do pagamento deve ser maior que zero.',
            'amount.max' => 'O valor do pagamento excede o limite máximo permitido.',
            'due_date.required' => 'A data de vencimento é obrigatória.',
            'due_date.date' => 'A data de vencimento deve ser uma data válida.',
            'status.required' => 'O status do pagamento é obrigatório.',
            'status.enum' => 'O status do pagamento é inválido.',
            'paid_at.date' => 'A data de pagamento deve ser uma data válida.',
            'payment_proof_path.max' => 'O caminho do comprovante não pode ter mais de 500 caracteres.',
        ];
    }
}

