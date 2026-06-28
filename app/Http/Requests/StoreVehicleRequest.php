<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'model' => ['required', 'string', 'max:190'],
            'brand' => ['required', 'string', 'max:190'],
            'plate' => ['required', 'alpha_num', 'size:7', 'unique:vehicles,plate'],
            'year' => ['nullable', 'digits:4'],
            'current_km' => ['nullable', 'integer', 'min:0'],
            'color' => ['nullable', 'string', 'max:80'],
            'fuel_type' => ['nullable', 'string', 'max:80'],
        ];
    }
}
