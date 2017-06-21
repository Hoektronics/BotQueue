<?php


namespace App\Validation;


interface CustomValidator
{
    public function passes($attribute, $value);

    public function message($attribute);
}