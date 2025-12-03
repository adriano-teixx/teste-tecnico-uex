<?php

namespace App\Http\Requests;

use App\Rules\Cpf;
use Illuminate\Foundation\Http\FormRequest;

abstract class ContactRequest extends FormRequest
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
            'cpf' => preg_replace('/\D/', '', (string) $this->input('cpf')),
            'cep' => preg_replace('/\D/', '', (string) $this->input('cep')),
            'state' => strtoupper((string) $this->input('state')),
        ]);
    }

    /**
     * Base rules shared between storing and updating contacts.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $cpfRules = [
            'required',
            'string',
            'size:11',
            new Cpf(),
            $this->uniqueCpfRule(),
        ];

        return [
            'name' => ['required', 'string', 'max:255'],
            'cpf' => $cpfRules,
            'phone' => ['required', 'string', 'max:20'],
            'cep' => ['required', 'string', 'size:8'],
            'street' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:10'],
            'complement' => ['nullable', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'size:2'],
        ];
    }

    /**
     * Get the rule that ensures CPF uniqueness per authenticated user.
     */
    abstract protected function uniqueCpfRule();
}
