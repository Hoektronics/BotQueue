<!doctype html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="/css/app.css">
    <livewire:styles />

    @yield('css')

    <title>BotQueue</title>
</head>
<body>
<div id="app">
    <nav class="flex border-b border-gray-400">
        <div class="flex-none my-auto ml-4">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 202 249" height="2.5rem">
                <a href="/">
                    <path d="M 64.20,12.39 C 61.24,18.13 59.54,24.62 59.54,31.52 59.56,39.97 62.13,48.22 66.91,55.18M 136.50,54.56 C 141.02,47.72 143.43,39.71 143.44,31.52 143.44,24.61 141.74,18.11 138.78,12.37M 77.84,21.50 C 76.44,24.71 75.66,28.24 75.66,31.95 75.67,37.73 77.59,43.35 81.12,47.93M 122.00,48.86 C 125.99,44.13 128.18,38.14 128.18,31.95 128.18,28.10 127.33,24.44 125.83,21.15M 102.96,21.11 C 95.85,21.11 90.08,26.87 90.08,33.98 90.08,39.55 93.68,44.49 98.99,46.21 98.99,46.21 99.11,82.32 98.99,87.26 98.93,89.61 106.05,89.61 106.05,87.26 106.03,82.38 106.05,46.46 106.05,46.46 111.79,45.04 115.83,39.89 115.83,33.98 115.83,26.87 110.07,21.11 102.96,21.11 102.96,21.11 102.96,21.11 102.96,21.11 Z M 94.57,149.43 C 94.57,149.43 10.63,106.94 10.63,106.94 10.63,106.94 10.63,191.93 10.63,191.93 10.63,191.93 94.57,237.77 94.57,237.77 94.57,237.77 94.57,149.43 94.57,149.43 Z M 191.37,107.54 C 191.37,107.54 107.44,150.04 107.44,150.04 107.44,150.04 107.44,238.38 107.44,238.38 107.44,238.38 191.37,192.53 191.37,192.53 191.37,192.53 191.37,107.54 191.37,107.54 Z M 182.62,129.96 C 182.62,129.96 179.97,185.74 179.97,185.74 179.97,185.74 164.52,194.35 164.52,194.35 164.52,194.35 165.85,138.91 165.85,138.91 165.85,138.91 182.62,129.96 182.62,129.96 Z M 138.61,149.41 C 138.61,149.41 135.95,205.19 135.95,205.19 135.95,205.19 120.50,213.80 120.50,213.80 120.50,213.80 121.83,158.36 121.83,158.36 121.83,158.36 138.61,149.41 138.61,149.41 Z M 112.79,55.65 C 112.79,69.16 112.64,84.67 112.71,92.30 112.75,97.25 92.29,97.90 92.28,92.95 92.28,84.66 92.12,70.32 92.12,55.30 92.12,55.30 14.14,95.13 14.14,95.13 14.14,95.13 100.25,137.25 100.25,137.25 100.25,137.25 189.55,93.57 189.55,93.57 189.55,93.57 116.89,58.10 116.89,58.10 116.89,58.10 112.79,55.65 112.79,55.65 Z"></path>
                </a>
            </svg>
        </div>
        <div class="flex-grow justify-between flex p-2">
            <a class="flex-none text-2xl hidden mr-4 lg:flex" href="/">BotQueue</a>
            <div class="flex flex-grow">
                @if(Auth::check())
                    <a class="my-auto mx-2 text-blue-500 hover:text-blue-800" href="{{ route('bots.index') }}">Bots</a>
                    <a class="my-auto mx-2 text-blue-500 hover:text-blue-800"
                       href="{{ route('clusters.index') }}">Clusters</a>
                    <a class="my-auto mx-2 text-blue-500 hover:text-blue-800" href="{{ route('jobs.index') }}">Jobs</a>
                    <a class="my-auto mx-2 text-blue-500 hover:text-blue-800"
                       href="{{ route('files.index') }}">Files</a>
                @endif
            </div>
            @if (Auth::guest())
                <div class="flex flex-none">
                    @if(setting('registration.enabled'))
                        <a class="my-auto mx-2 text-blue-500 hover:text-blue-800"
                           href="{{ route('register') }}">Register</a>
                    @endif
                    <a class="my-auto mx-2 text-blue-500 hover:text-blue-800" href="{{ route('login') }}">Login</a>
                </div>
            @else
                <div class="hidden md:flex flex-none">
                    <a class="invisible md:visible my-auto mx-2 text-blue-500 hover:text-blue-800" href="#"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST"
                          style="display: none;">
                        {{ csrf_field() }}
                    </form>
                </div>
            @endif
        </div>
    </nav>

    <div class="mt-4 mx-4">
        @yield('content')
    </div>
</div>

<script src="{{ mix('js/app.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.js"></script>
@yield('script')
<livewire:scripts />
</body>
</html>
