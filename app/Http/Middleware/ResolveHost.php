<?php

namespace App\Http\Middleware;

use App\Host;
use App\HostManager;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Response;
use Lcobucci\JWT\Parser;

class ResolveHost
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->hasHeader('authorization')) {
            return $next($request);
        }

        $header = $request->header('authorization');
        $jwt = trim(preg_replace('/^(?:\s+)?Bearer\s/', '', $header));

        $token = (new Parser())->parse($jwt);
        $jti = $token->getClaim('jti');

        $host = Host::where('token_id', $jti)->first();

        $host->seen_at = Carbon::now();
        $host->save();

        app(HostManager::class)->setHost($host);

        return $next($request);
    }
}
