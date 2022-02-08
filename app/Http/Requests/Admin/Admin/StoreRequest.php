<?php

namespace App\Http\Requests\Admin\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'name' => 'required|max:190',
            'email' => 'required|max:190|email|unique:' . Admin::class,
            'password' => 'required|confirmed|min:8|max:60',
        ];
    }
}
