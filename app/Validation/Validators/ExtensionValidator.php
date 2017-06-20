<?php


namespace App\Validation\Validators;


use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class ExtensionValidator implements CustomValidator
{
    /**
     * @var array
     */
    private $extensions;

    public function __construct($extensions) {

        $this->extensions = $extensions;
    }
    /**
     * @param $attribute
     * @param $value UploadedFile
     * @param $parameters
     * @param $validator
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $originalName = $value->getClientOriginalName();
        return in_array(File::extension($originalName), $this->extensions);
    }

    public function message($attribute)
    {
        $values = implode(', ', $this->extensions);
        return "The ${attribute} must be a file with extension: ${values}.";
    }

    public function __toString()
    {
        return 'extension:'.serialize($this);
    }
}