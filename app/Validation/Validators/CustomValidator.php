<?php


namespace App\Validation\Validators;


interface CustomValidator
{
    public function passes($attribute, $value, $parameters, $validator);

    public function replacer($message, $attribute, $rule, $parameters);
}