<?php

namespace App\ModelTraits;


use App\Models\User;

trait FirstUserIsPromotedToAdmin
{
    public static function bootFirstUserIsPromotedToAdmin()
    {
        static::creating(function (User $user) {
            if(User::count() == 0) {
                $user->is_admin = true;
            }
        });
    }
}