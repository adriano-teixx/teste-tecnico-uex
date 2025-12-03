<?php

namespace App\Http\Requests;

use App\Models\Contact;
use Illuminate\Validation\Rule;

class StoreContactRequest extends ContactRequest
{
    /**
     * Get the unique CPF rule for storing contacts.
     */
    protected function uniqueCpfRule()
    {
        return Rule::unique(Contact::class)->where(fn ($query) => $query->where('user_id', $this->user()->id));
    }
}
