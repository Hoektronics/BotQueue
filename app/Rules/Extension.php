<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class Extension implements Rule
{
    protected $extensions;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($extensions)
    {
        $this->extensions = $extensions;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  UploadedFile  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $originalName = $value->getClientOriginalName();
        return in_array(File::extension($originalName), $this->extensions);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $values = implode(', ', $this->extensions);
        return "The :attribute must be a file with extension: ${values}.";
    }
}
