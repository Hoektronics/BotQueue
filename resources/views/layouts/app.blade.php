<!doctype html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="/css/main.css">

    @yield('css')

    <title>BotQueue</title>
</head>
<body>
    <nav class="flex border-b border-gray-400 justify-between p-4">
        <a class="flex-none text-2xl" href="/">BotQueue</a>
        <div class="flex flex-grow mx-8">
            @if(Auth::check())
                <a class="my-auto mx-2 text-blue-500 hover:text-blue-800" href="{{ route('bots.index') }}">Bots</a>
                <a class="my-auto mx-2 text-blue-500 hover:text-blue-800" href="{{ route('clusters.index') }}">Clusters</a>
                <a class="my-auto mx-2 text-blue-500 hover:text-blue-800" href="{{ route('jobs.index') }}">Jobs</a>
                <a class="my-auto mx-2 text-blue-500 hover:text-blue-800" href="{{ route('files.index') }}">Files</a>
            @endif
        </div>
        @if (Auth::guest())
            <div class="flex flex-none">
            <a class="my-auto mx-2 text-blue-500 hover:text-blue-800" href="{{ route('register') }}">Register</a>
            <a class="my-auto mx-2 text-blue-500 hover:text-blue-800" href="{{ route('login') }}">Login</a>
            </div>
        @endif
    </nav>
<!--


            <div class="" id="main-navbar-collapse">
                <ul class="flex">
                    @if (Auth::check())
    <li class="">
        <a href="{{ route('bots.index') }}">Bots</a>
                        </li>
                        <li class="">
                            <a href="{{ route('clusters.index') }}">Clusters</a>
                        </li>
                        <li class="">
                            <a href="{{ route('jobs.index') }}">Jobs</a>
                        </li>
                        <li class="">
                            <a href="{{ route('files.index') }}">Files</a>
                        </li>
                    @endif
        </ul>

        <ul class="flex">
@if (Auth::guest())
    <li class="">
        <a href="{{ route('register') }}">Register</a>
                        </li>
                        <li class="">
                            <a href="{{ route('login') }}">Login</a>
                        </li>
                    @else
    <li class="">
        <a href="#" class="" data-toggle="" role="button"
           aria-expanded="false">
{{ Auth::user()->username }} <span class="caret"></span>
                            </a>

                            <div class="" role="menu">
                                <a class="" href="#"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    Logout
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                      style="display: none;">
                                    {{ csrf_field() }}
            </form>
        </div>
    </li>
@endif
        </ul>
    </div>
</nav>
-->

    <div class="container">
        @yield('content')
    </div>

@yield('script')
</body>
</html>
