<?php


namespace App\Validation;


interface ValidationRule
{
    public function passes($attribute, $value, $parameters, $validator);

    public function replacer($message, $attribute, $rule, $parameters);
}