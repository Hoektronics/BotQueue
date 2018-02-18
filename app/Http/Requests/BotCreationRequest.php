<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BotCreationRequest extends FormRequest
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
            'name' => 'required|unique:bots,name|max:255',
            'type' => [
                'required',
                Rule::in(['3d_printer'])
            ],
            'cluster' => [
                'required',
                Rule::exists('clusters', 'id')->where(function ($query) {
                    $query->where('creator_id', Auth::user()->id);
                })
            ],
        ];
    }
}
