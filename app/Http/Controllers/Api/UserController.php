<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\User;

class UserController extends Controller
{
    public function show(User $user)
    {
        return new UserResource($user);
    }
}
