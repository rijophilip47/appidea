<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Family membership and specific permissions handled in Controller/Policy
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'category_id' => ['required', 'integer', 'exists:document_categories,id'],
            'issuer' => ['nullable', 'string', 'max:120'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'issue_date' => ['nullable', 'date'],
            'purchase_date' => ['nullable', 'date'],
            'expiry_date' => [
                'nullable',
                'date',
                function ($attribute, $value, $fail) {
                    if ($value && $this->filled('issue_date')) {
                        if (strtotime($value) < strtotime($this->input('issue_date'))) {
                            $fail('The expiry date cannot be earlier than the issue date.');
                        }
                    }
                },
            ],
            'responsible_user_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'attachment.max' => 'The document attachment must not be larger than 10 MB.',
            'attachment.mimes' => 'Only PDF, JPG, JPEG, and PNG files are allowed as attachments.',
        ];
    }
}
