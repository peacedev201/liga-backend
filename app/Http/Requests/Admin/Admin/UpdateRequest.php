<?php

namespace App\Http\Requests\Admin\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
        $storeRequest = new StoreRequest();
        $storeRules = $storeRequest->rules();
        $updateRules = [
            'email' => 'required|max:190|email|unique:' . Admin::class . ',email,' . $this->admin,
            'password' => 'sometimes|confirmed|min:8|max:60',
        ];
        $rules = array_merge($storeRules, $updateRules);
        return $rules;
    }
}
