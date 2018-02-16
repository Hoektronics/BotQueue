<?php

namespace App\Http\Controllers\Api\V2;

use App\Bot;
use App\Http\Resources\BotResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class BotController extends Controller
{
    public function index()
    {
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
