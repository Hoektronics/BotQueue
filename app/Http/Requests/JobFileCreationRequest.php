<?php

namespace App\Http\Requests;

use App;
use App\Validation\MatchExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class JobFileCreationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'bot_cluster' => [
                'required',
                new MatchExists([
                    'bots_{id}' => App\Bot::mine(),
                    'clusters_{id}' => App\Cluster::mine(),
                ]),
            ]
        ];
    }
}
