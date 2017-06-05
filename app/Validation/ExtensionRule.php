<?php


namespace App\Validation;


use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class ExtensionRule implements ValidationRule
{

    /**
     * @param $attribute
     * @param $value UploadedFile
     * @param $parameters
     * @param $validator
     * @return bool
     */
    public function passes($attribute, $value, $parameters, $validator)
    {
        $originalName = $value->getClientOriginalName();
        return in_array(File::extension($originalName), $parameters);
    }

    public function replacer($message, $attribute, $rule, $parameters)
    {
        return str_replace(':values', implode(', ', $parameters), $message);
    }
}