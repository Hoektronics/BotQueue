@extends('layouts.app')

@section('content')
    <div class="page-header">
        <div class="btn-toolbar pull-right">
            <a role="button" class="btn btn-primary btn-lg" href="/bot/create">Create a Bot</a>
        </div>
        <h1>Bots</h1>
    </div>

    @foreach($bots as $bot)
        <div class="row">
            <div class="col-md-3">
                {{ $bot->name }}
            </div>
            <div class="col-md-3">
                {{ $bot->status }}
            </div>
        </div>
    @endforeach
@endsection