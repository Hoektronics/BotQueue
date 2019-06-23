<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BotUpdateRequest extends FormRequest
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

    public function validator(ValidationFactory $factory)
    {
        $validator = $factory->make($this->validationData(), [
            'name' => 'sometimes|filled',
            'driver' => [
                'sometimes',
                'filled',
                Rule::in('printrun', 'dummy'),
            ]
        ]);

        $validator->sometimes('serial_port', 'required', function($input) {
            return $this->isPrintrunDriver($input);
        });

        $validator->sometimes('delay', 'sometimes|numeric', function($input) {
            return $this->isDummyDriver($input);
        });

        return $validator;
    }

    protected function isPrintrunDriver($input)
    {
        return $input->driver === 'printrun';
    }

    protected function isDummyDriver($input)
    {
        return $input->driver === 'dummy';
    }
}
