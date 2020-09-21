<?php

namespace App\Http\Requests;

use App\Enums\DriverType;
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
                Rule::in(DriverType::allDrivers()),
            ],
        ]);

        $validator->sometimes('serial_port', 'required', function ($input) {
            return $this->isGcodeDriver($input);
        });

        $validator->sometimes('baud_rate', 'required', function ($input) {
            return $this->isGcodeDriver($input);
        });

        $validator->sometimes('delay', 'sometimes|numeric', function ($input) {
            return $this->isDummyDriver($input);
        });

        return $validator;
    }

    protected function isGcodeDriver($input)
    {
        return $input->driver === DriverType::GCODE;
    }

    protected function isDummyDriver($input)
    {
        return $input->driver === DriverType::DUMMY;
    }
}
