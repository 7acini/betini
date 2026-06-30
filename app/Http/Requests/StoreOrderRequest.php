<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'payment_method' => ['nullable', 'string', 'max:80'],
            'status' => ['required', 'string', Rule::in(['Aberto', 'Em andamento', 'Concluido', 'Cancelado'])],
            'observation' => ['nullable', 'string', 'max:5000'],
            'service_total' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'services' => ['nullable', 'array'],
            'services.*.service_id' => ['required_with:services', 'integer', 'exists:services,id'],
            'services.*.quantity' => ['required_with:services', 'integer', 'min:1', 'max:9999'],
            'services.*.price' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'items' => ['nullable', 'array'],
            'items.*.product_id' => ['required_with:items', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1', 'max:9999'],
            'items.*.price' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
        ];
    }
}
