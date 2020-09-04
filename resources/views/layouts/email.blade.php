<!doctype html>
<html lang="{{ config('app.locale') }}">
    <head>
        <style> {!! readfile(public_path('css/app.css')) !!} </style>
    </head>
    <body>
        @yield('content')
    </body>
</html>