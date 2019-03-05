<?php

namespace App\Http\Controllers;

use App\Errors\HostErrors;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Http\Request;

class HostApiController extends Controller
{
    public function command(Request $request)
    {
        $commandName = $request->input("command");

        $classpath = "App\\Http\\HostCommands\\${commandName}Command";

        if (class_exists($classpath)) {
            $command = app()->make($classpath);
            $data = collect($request->input("data", []));

            if (method_exists($command, "verifyAuth")) {
                $command->verifyAuth(app()->make(Auth::class));
            }

            return $command($data);
        } else {
            return HostErrors::invalidCommand();
        }
    }
}
