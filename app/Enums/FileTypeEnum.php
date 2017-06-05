<?php

namespace App\Enums;


class FileTypeEnum
{
    const GCode = "gcode";
    const STL = "stl";

    public static function fromExtension($extension)
    {
        switch ($extension) {
            case "gcode":
                return self::GCode;
            case "stl":
                return self::STL;
            default:
                throw new \Exception("$extension is not a recognized file type");
        }
    }
}