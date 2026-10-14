<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InviteFamilyMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'string', 'in:admin,member'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Please provide the email address of the family member to invite.',
            'role.in' => 'The selected role must be either Administrator or Member.',
        ];
    }
}
