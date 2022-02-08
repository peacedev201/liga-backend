<?php

namespace App\Http\Requests\User\Auth;

use App\Models\User;
use App\Models\ClubProfile;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'type' => 'required|in:club,player',
            'biography' => 'nullable|string',
            'email' => 'required|email|unique:' . User::class,
            'password' => 'required|min:8|max:190|confirmed',

            // Player
            'first_name' => 'exclude_if:type,club|required|string|max:190',
            'last_name' => 'exclude_if:type,club|required|string|max:190',
            'nick_name' => 'exclude_if:type,club|required|string|max:190',
            'postal_code' => 'exclude_if:type,club|required|string|max:190',
            'street' => 'exclude_if:type,club|required|string|max:190',
            'city' => 'exclude_if:type,club|required|string|max:190',
            'country' => 'exclude_if:type,club|required|string|max:190',
            'club' => 'exclude_if:type,club|nullable|exists:' . ClubProfile::class . ',id',
            'is_member' => 'exclude_if:type,club|boolean',

            // Club
            'name' => 'exclude_if:type,player|required|string|max:190',
        ];
    }
}
