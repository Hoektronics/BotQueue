<?php

namespace App\Http\Controllers\Api;

use App\Bot;
use App\Http\Controllers\Controller;
use App\Http\Resources\BotResource;
use App\User;
use Illuminate\Support\Facades\Auth;

class BotController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        $bots = $user->bots()->with('creator')->get();

        return BotResource::collection($bots);
    }

    public function show(Bot $bot)
    {
        $bot->load('creator');

        return new BotResource($bot);
    }
}
