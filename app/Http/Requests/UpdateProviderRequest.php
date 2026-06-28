<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProviderRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'cnpj' => preg_replace('/\D+/', '', (string) $this->input('cnpj')),
            'phone' => preg_replace('/\D+/', '', (string) $this->input('phone')) ?: null,
            'postal_code' => preg_replace('/\D+/', '', (string) $this->input('postal_code')) ?: null,
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $providerId = $this->route('provider')?->id;

        return [
            'name' => ['required', 'string', 'min:3', 'max:190'],
            'cnpj' => ['required', 'digits:14', Rule::unique('providers', 'cnpj')->ignore($providerId)],
            'phone' => ['nullable', 'string', 'min:10', 'max:11'],
            'postal_code' => ['nullable', 'digits:8'],
            'address' => ['nullable', 'string', 'max:190'],
            'address_number' => ['nullable', 'string', 'max:20'],
            'complement' => ['nullable', 'string', 'max:80'],
            'city' => ['nullable', 'string', 'max:190'],
            'state' => ['nullable', 'alpha', 'size:2'],
            'website_url' => ['nullable', 'url', 'max:190'],
        ];
    }
}
