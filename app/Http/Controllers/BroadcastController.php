<?php

namespace App\Http\Controllers;

use App\Managers\BroadcastAuthManager;
use Illuminate\Http\Request;

class BroadcastController extends Controller
{
    public function auth(Request $request, BroadcastAuthManager $authManager)
    {
        return $authManager->auth($request);
    }
}
