<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:190'],
            'cpf' => ['required', 'digits:11', 'unique:clients,cpf'],
            'phone' => ['nullable', 'string', 'min:10', 'max:11'],
            'postal_code' => ['nullable', 'digits:8'],
            'address' => ['nullable', 'string', 'max:190'],
            'address_number' => ['nullable', 'string', 'max:20'],
            'complement' => ['nullable', 'string', 'max:80'],
            'city' => ['nullable', 'string', 'max:190'],
            'state' => ['nullable', 'alpha', 'size:2'],
        ];
    }
}
