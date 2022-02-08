<?php

namespace App\Http\Requests\Admin\Tournament;

use App\Models\TournamentRound;
use Illuminate\Foundation\Http\FormRequest;

class RoundRequest extends FormRequest
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
            'rounds.*.id' => 'sometimes|exists:' . TournamentRound::class . ',id',
            'rounds.*.name' => 'required|max:190',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $rounds = array_map(
            function ($x) {
                $round = [
                    'name' => $x['name'],
                ];
                if (isset($x['id'])) {
                    $round['id'] = $x['id'];
                }
                return $round;
            },
            $this->rounds
        );
        $this->merge([
            'rounds' => $rounds,
        ]);
    }
}
