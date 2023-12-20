<?php

namespace App\Http\Request;

use App\Models\ApplicantUser;
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
            $user = ApplicantUser::find($this->route('id'));
            if ( hash_equals((string) $this->route('hash'), sha1($user->getEmailForVerification())) ) {
                return true;
            }
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
     * Fulfill the email verification request without Login.
     *
     * @return void
     */
    public function autofill()
    {
        $user = ApplicantUser::find($this->route('id'));
        
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
            
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
