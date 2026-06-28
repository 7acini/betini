<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $clientId = $this->route('client')?->id;

        return [
            'name' => ['required', 'string', 'min:3', 'max:190'],
            'cpf' => ['required', 'digits:11', Rule::unique('clients', 'cpf')->ignore($clientId)],
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
