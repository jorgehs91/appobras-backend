<?php

namespace App\Http\Requests\PurchaseRequest;

use App\Enums\PurchaseRequestStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by controller/policy
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'status' => ['sometimes', 'string', Rule::enum(PurchaseRequestStatus::class), Rule::in([PurchaseRequestStatus::draft->value])],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.cost_item_id' => ['nullable', 'integer', 'exists:cost_items,id'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
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
            'supplier_id.required' => 'O fornecedor é obrigatório.',
            'supplier_id.exists' => 'O fornecedor selecionado não existe.',
            'status.in' => 'Ao criar uma requisição de compra, o status deve ser "draft".',
            'notes.max' => 'As observações não podem ter mais de 1000 caracteres.',
            'items.required' => 'É necessário adicionar pelo menos um item à requisição de compra.',
            'items.min' => 'É necessário adicionar pelo menos um item à requisição de compra.',
            'items.*.cost_item_id.exists' => 'Um dos itens de custo selecionados não existe.',
            'items.*.description.required' => 'A descrição do item é obrigatória.',
            'items.*.description.max' => 'A descrição do item não pode ter mais de 500 caracteres.',
            'items.*.quantity.required' => 'A quantidade do item é obrigatória.',
            'items.*.quantity.min' => 'A quantidade do item deve ser maior que zero.',
            'items.*.unit_price.required' => 'O preço unitário do item é obrigatório.',
            'items.*.unit_price.numeric' => 'O preço unitário do item deve ser um número.',
            'items.*.unit_price.min' => 'O preço unitário do item não pode ser negativo.',
            'items.*.unit_price.max' => 'O preço unitário do item excede o limite máximo permitido.',
        ];
    }
}

