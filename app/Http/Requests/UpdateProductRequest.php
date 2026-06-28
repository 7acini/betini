<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'barcode' => $this->filled('barcode') ? preg_replace('/\D+/', '', (string) $this->input('barcode')) : null,
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'provider_id' => ['nullable', 'integer', 'exists:providers,id'],
            'category' => ['nullable', 'string', 'max:120'],
            'name' => ['required', 'string', 'min:2', 'max:190'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'description' => ['nullable', 'string', 'max:5000'],
            'barcode' => ['nullable', 'string', 'max:80', Rule::unique('products', 'barcode')->ignore($productId)],
            'photo_path' => ['nullable', 'string', 'max:190'],
        ];
    }
}
