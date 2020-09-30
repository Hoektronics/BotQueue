<?php

namespace Tests\Helpers\Models;


trait Builder
{
    private static $known_ids = [];

    protected function get_id()
    {
        do {
            $id = rand(1, 1000000);
        } while(in_array($id, self::$known_ids));
        self::$known_ids[] = $id;

        return $id;
    }
}