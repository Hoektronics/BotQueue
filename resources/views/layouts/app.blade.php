<!doctype html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="stylesheet"
              href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
              integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm"
              crossorigin="anonymous">
        @yield('css')

        <title>BotQueue</title>
    </head>
    <body>
        <nav class="navbar fixed-top navbar-expand-md navbar-light bg-light">
            <a class="navbar-brand" href="/">BotQueue</a>

            <div class="collapse navbar-collapse" id="main-navbar-collapse">
                <ul class="navbar-nav mr-auto">
                    @if (Auth::check())
                        <li class="nav-item nav-link">
                            <a href="{{ route('bots.index') }}">Bots</a>
                        </li>
                        <li class="nav-item nav-link">
                            <a href="{{ route('clusters.index') }}">Clusters</a>
                        </li>
                        <li class="nav-item nav-link">
                            <a href="{{ route('jobs.index') }}">Jobs</a>
                        </li>
                        <li class="nav-item nav-link">
                            <a href="{{ route('files.index') }}">Files</a>
                        </li>
                    @endif
                </ul>

                <ul class="navbar-nav">
                    @if (Auth::guest())
                        <li class="nav-item nav-link">
                            <a href="{{ route('register') }}">Register</a>
                        </li>
                        <li class="nav-item nav-link">
                            <a href="{{ route('login') }}">Login</a>
                        </li>
                    @else
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                               aria-expanded="false">
                                {{ Auth::user()->username }} <span class="caret"></span>
                            </a>

                            <div class="dropdown-menu dropdown-menu-right" role="menu">
                                <a class=" dropdown-item nav-link text-center" href="#"
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

        <div class="container" style="padding-top: 70px;">
            @yield('content')
        </div>
        <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
                integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
                crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
                integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
                crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
                integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
                crossorigin="anonymous"></script>


        @yield('script')
    </body>
</html>
