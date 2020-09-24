<?php

namespace App\Enums;


class DriverType
{
    const GCODE = "gcode";
    const DUMMY = "dummy";

    public static function allDrivers()
    {
        return collect([
            self::GCODE,
            self::DUMMY,
        ]);
    }
}