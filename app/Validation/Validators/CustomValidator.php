<?php


namespace App\Validation\Validators;


interface CustomValidator
{
    public function passes($attribute, $value);

    public function message($attribute);
}