<?php

namespace App\Http\Requests\Admin\Tournament;

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
        return [
            'name' => 'required|max:190',
            'icon_image' => 'image',
            'banner_image' => 'image',
            'system' => 'required|max:190',
            'description' => 'required',
            'only_for_clubs' => 'required|string',
            'registration_end_date_time' => 'required|date:Y-m-d H:i:s|after_or_equal:now',
            'total_slots' => 'required|in:8,16,32,64,128,256',
            'open_for_registration' => 'sometimes|string',
            'close_for_registration' => 'sometimes|string',
        ];
    }
}
