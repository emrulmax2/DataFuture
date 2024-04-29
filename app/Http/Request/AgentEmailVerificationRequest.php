<?php

namespace App\Http\Request;

use App\Models\AgentUser;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Http\FormRequest;

class AgentEmailVerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if($this->user('agent')==NULL) { 
            $user = AgentUser::find($this->route('id'));
            if ( hash_equals((string) $this->route('hash'), sha1($user->getEmailForVerification())) ) {
                return true;
            }
            return false;
        } else {
            if (! hash_equals((string) $this->user('agent')->getKey(), (string) $this->route('id'))) {
                return false;
            }

            if (! hash_equals(sha1($this->user('agent')->getEmailForVerification()), (string) $this->route('hash'))) {
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
        if (! $this->user('agent')->hasVerifiedEmail()) {
            $this->user('agent')->markEmailAsVerified();

            event(new Verified($this->user('agent')));
        }
    }
    /**
     * Fulfill the email verification request without Login.
     *
     * @return void
     */
    public function autofill()
    {
        $user = AgentUser::find($this->route('id'));
        
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
