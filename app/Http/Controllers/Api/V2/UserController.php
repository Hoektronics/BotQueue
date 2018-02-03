<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\UserResource;
use App\User;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function show(User $user)
    {
        return new UserResource($user);
    }
}
