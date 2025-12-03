<?php

namespace App\Http\Requests;

use App\Models\Contact;
use Illuminate\Validation\Rule;

class UpdateContactRequest extends ContactRequest
{
    /**
     * Get the unique CPF rule for updating contacts.
     */
    protected function uniqueCpfRule()
    {
        $contact = $this->route('contact');

        return Rule::unique(Contact::class)
            ->where(fn ($query) => $query->where('user_id', $this->user()->id))
            ->ignore($contact?->id);
    }
}
