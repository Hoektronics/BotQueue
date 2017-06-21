<?php

namespace App\Http\Requests;

use App;
use App\Validation\MatchExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class JobFileCreationRequest extends FormRequest
{
    /** @var MatchExists $matchExists */
    private $matchExists;

    /**
     * Validate the class instance.
     *
     * @return void
     */
    public function validate()
    {
        parent::validate();

        $original_value = $this->get('bot_cluster');

        $this->merge([
            'bot_cluster' => $this->matchExists->getModel($original_value)
        ]);
    }

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
        $this->matchExists = new MatchExists([
            'bots_{id}' => App\Bot::mine(),
            'clusters_{id}' => App\Cluster::mine(),
        ]);

        return [
            'bot_cluster' => [
                'required',
                $this->matchExists,
            ]
        ];
    }
}
