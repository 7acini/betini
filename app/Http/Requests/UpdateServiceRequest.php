<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:190'],
            'description' => ['nullable', 'string', 'max:5000'],
            'base_price' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
        ];
    }
}
