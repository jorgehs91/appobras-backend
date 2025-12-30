<?php

namespace App\Http\Requests\Expense;

use App\Enums\ExpenseStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExpenseRequest extends FormRequest
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
            'cost_item_id' => ['nullable', 'integer', 'exists:cost_items,id'],
            'project_id' => ['sometimes', 'integer', 'exists:projects,id'],
            'amount' => ['sometimes', 'numeric', 'min:0.01', 'max:9999999999999.99'],
            'date' => ['sometimes', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'receipt' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'], // 10MB max
            'receipt_path' => ['nullable', 'string', 'max:500'], // For direct path input
            'status' => ['sometimes', 'string', Rule::enum(ExpenseStatus::class)],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // If status is approved, receipt is required
            $status = $this->input('status');
            $expense = $this->route('expense');
            
            if ($status === ExpenseStatus::approved->value || 
                ($expense && $expense->status === ExpenseStatus::approved && ! $status)) {
                // Check if receipt exists (file upload, path update, or existing in model)
                $hasReceipt = $this->hasFile('receipt') || 
                             $this->input('receipt_path') || 
                             ($expense && $expense->receipt_path);
                
                if (! $hasReceipt) {
                    $validator->errors()->add('receipt', 'O comprovante é obrigatório para despesas aprovadas.');
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'project_id.exists' => 'O projeto selecionado não existe.',
            'cost_item_id.exists' => 'O item de custo selecionado não existe.',
            'amount.numeric' => 'O valor da despesa deve ser um número.',
            'amount.min' => 'O valor da despesa deve ser maior que zero.',
            'amount.max' => 'O valor da despesa excede o limite máximo permitido.',
            'date.date' => 'A data da despesa deve ser uma data válida.',
            'description.max' => 'A descrição não pode ter mais de 1000 caracteres.',
            'receipt.file' => 'O comprovante deve ser um arquivo.',
            'receipt.mimes' => 'O comprovante deve ser um arquivo PDF, JPG, JPEG ou PNG.',
            'receipt.max' => 'O comprovante não pode ter mais de 10MB.',
            'receipt_path.max' => 'O caminho do comprovante não pode ter mais de 500 caracteres.',
            'status.enum' => 'O status da despesa é inválido.',
        ];
    }
}

