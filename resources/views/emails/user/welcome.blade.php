@extends('layouts.email')

@section('content')
    Hello, {{ $user->username }}

    Thank you so much for signing up for BotQueue! We hope you enjoy using it!
@endsection