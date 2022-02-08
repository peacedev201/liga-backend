<?php

namespace App\Http\Requests\Admin\Tournament;

use App\Models\PlayerProfile;
use App\Models\TournamentRound;
use Illuminate\Foundation\Http\FormRequest;

class ScheduleRequest extends FormRequest
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
            'rounds.*.id' => 'required|exists:' . TournamentRound::class . ',id',
            'rounds.*.matches' => 'required|array',
            'rounds.*.matches.*.first_player_id' => 'nullable|exists:' . PlayerProfile::class . ',id',
            'rounds.*.matches.*.second_player_id' => 'nullable|exists:' . PlayerProfile::class . ',id',
            'rounds.*.matches.*.held_date' => 'nullable|date_format:Y-m-d',
            'rounds.*.matches.*.held_time' => 'nullable|date_format:H:i:s',
            'rounds.*.matches.*.first_player_score' => 'nullable|numeric',
            'rounds.*.matches.*.second_player_score' => 'nullable|numeric',
            'rounds.*.matches.*.first_player_points' => 'nullable|numeric',
            'rounds.*.matches.*.second_player_points' => 'nullable|numeric',
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
                    'id' => $x['id'],
                    'matches' => []
                ];
                foreach ($x['matches'] as $match) {
                    $single = [];
                    if (isset($match['first_player_id'])) {
                        $single['first_player_id'] = $match['first_player_id'];
                    } else {
                        $single['first_player_id'] = null;
                    }
                    if (isset($match['second_player_id'])) {
                        $single['second_player_id'] = $match['second_player_id'];
                    } else {
                        $single['second_player_id'] = null;
                    }
                    if (isset($match['held_date'])) {
                        $single['held_date'] = $match['held_date'];
                    } else {
                        $single['held_date'] = null;
                    }
                    if (isset($match['held_time'])) {
                        $single['held_time'] = $match['held_time'];
                    } else {
                        $single['held_time'] = null;
                    }
                    if (isset($match['first_player_score'])) {
                        $single['first_player_score'] = $match['first_player_score'];
                    } else {
                        $single['second_player_score'] = null;
                    }
                    if (isset($match['second_player_score'])) {
                        $single['second_player_score'] = $match['second_player_score'];
                    } else {
                        $single['second_player_score'] = null;
                    }
                    if (isset($match['first_player_points'])) {
                        $single['first_player_points'] = $match['first_player_points'];
                    } else {
                        $single['first_player_points'] = null;
                    }
                    if (isset($match['second_player_points'])) {
                        $single['second_player_points'] = $match['second_player_points'];
                    } else {
                        $single['second_player_points'] = null;
                    }
                    array_push($round['matches'], $single);
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
