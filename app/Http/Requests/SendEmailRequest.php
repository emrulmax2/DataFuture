<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendEmailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'comon_smtp_id' => 'required',
            'subject' => 'required',
            'body' => 'required',
            'bcc_emails' => ['nullable', function ($attribute, $value, $fail) {
                $invalid = array_filter($this->bccEmails(), fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL) === false);
                if (!empty($invalid)) {
                    $fail('Invalid BCC email: '.implode(', ', $invalid).'. Separate multiple emails with a comma.');
                }
            }],
        ];
    }

    /**
     * BCC addresses split on commas, semicolons or whitespace.
     *
     * @return array<int, string>
     */
    public function bccEmails()
    {
        $emails = preg_split('/[\s,;]+/', (string) $this->input('bcc_emails', ''), -1, PREG_SPLIT_NO_EMPTY);

        return array_values(array_unique($emails));
    }

    public function messages()
    {
        return [
            'comon_smtp_id.required' => 'SMTP field is required',
            'subject.required' => 'Subject can not be empty',
            'body.required' => 'Mail body should not be empty.'
        ];
    }
}
