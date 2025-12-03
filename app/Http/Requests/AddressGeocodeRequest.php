<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressGeocodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'state' => strtoupper((string) $this->input('state')),
            'street' => trim((string) $this->input('street')),
            'city' => trim((string) $this->input('city')),
            'district' => trim((string) $this->input('district')),
            'number' => trim((string) $this->input('number')),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'street' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'alpha', 'size:2'],
            'district' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:10'],
        ];
    }
}
