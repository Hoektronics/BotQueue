<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\BotResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class BotController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return BotResource::collection($user->bots);
    }
}
