<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressSearchRequest extends FormRequest
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
            'uf' => strtoupper((string) $this->input('uf')),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'uf' => ['required', 'alpha', 'size:2'],
            'city' => ['required', 'string', 'max:255'],
            'street' => ['required', 'string', 'min:3', 'max:255'],
        ];
    }
}
