<?php

namespace App\Http\Request;

use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Http\FormRequest;

class EmailVerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if($this->user('applicant')==NULL) { 
            return false;
        } else {
            if (! hash_equals((string) $this->user('applicant')->getKey(), (string) $this->route('id'))) {
                return false;
            }

            if (! hash_equals(sha1($this->user('applicant')->getEmailForVerification()), (string) $this->route('hash'))) {
                return false;
            }
        
            return true;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }

    /**
     * Fulfill the email verification request.
     *
     * @return void
     */
    public function fulfill()
    {
        if (! $this->user('applicant')->hasVerifiedEmail()) {
            $this->user('applicant')->markEmailAsVerified();

            event(new Verified($this->user('applicant')));
        }
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        return $validator;
    }
}
