<?php

namespace App\Enums;

use App;
use Illuminate\Support\Facades\File;

class FileTypeEnum
{
    const GCODE = "gcode";
    const STL = "stl";

    public static function fromFile($file)
    {
        if (is_a($file, App\File::class)) {
            $file = $file->name;
        }
        $extension = File::extension($file);
        return self::fromExtension($extension);
    }

    public static function fromExtension($extension)
    {
        switch ($extension) {
            case "gcode":
                return self::GCODE;
            case "stl":
                return self::STL;
            default:
                throw new \Exception("$extension is not a recognized file type");
        }
    }
}
