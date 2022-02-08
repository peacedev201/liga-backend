<?php

namespace App\Http\Requests\User\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ResetRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|email|exists:' . User::class,
            'password' => 'required|min:8|max:60|confirmed',
            'token' => 'required'
        ];
    }
}
