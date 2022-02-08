<?php

namespace App\Http\Requests\User\Player;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
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
            'first_name' => 'required|max:190',
            'last_name' => 'required|max:190',
            'nick_name' => 'required|max:190',
            'postal_code' => 'required|max:190',
            'street' => 'required|max:190',
            'city' => 'required|max:190',
            'country' => 'required|max:190',
            'biography' => 'nullable|string',
            'avatar' => 'image',
            'setting' => 'required|array',
            'setting.name' => 'required|in:first_nick,last_nick,first_last_nick',
        ];
    }
}
