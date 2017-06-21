<?php


namespace App\Validation;


use App\Providers\ValidationHolder;

trait CustomValidatorTrait
{
    protected $custom_validator_id;

    public function __toString()
    {
        if(! isset($this->custom_validator_id)) {
            $this->custom_validator_id = uniqid();

            /** @var ValidationHolder $validationHolder */
            $validationHolder = app(ValidationHolder::class);
            $validationHolder->add($this, $this->custom_validator_id);
        }

        return "custom_validator:".$this->custom_validator_id;
    }
}