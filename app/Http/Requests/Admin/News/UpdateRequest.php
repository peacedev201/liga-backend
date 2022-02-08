<?php

namespace App\Http\Requests\Admin\News;

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
            'title' => 'required|string|max:190|unique:news,title,' . $this->news,
            'image' => 'image',
            'content_image' => 'image',
        ];
        $rules = array_merge($storeRules, $updateRules);
        return $rules;
    }
}
