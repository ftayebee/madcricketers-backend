<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MatchTeamInfoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'batting_team'  => (int) $this->batting_team,
            'bowling_team'  => (int) $this->bowling_team,
            'match_id'      => (int) $this->match_id,
            'tournament_id' => $this->tournament_id !== null
                ? (int) $this->tournament_id
                : null,
            'is_tournament' => filter_var(
                $this->is_tournament,
                FILTER_VALIDATE_BOOLEAN
            ),
        ]);
    }

    public function rules()
    {
        return [
            'batting_team'  => 'required|integer|exists:teams,id',
            'bowling_team'  => 'required|integer|exists:teams,id',
            'match_id'      => 'required|integer|exists:cricket_matches,id',
            'is_tournament' => 'required|boolean',
            'tournament_id' => 'nullable|integer',
        ];
    }
}
