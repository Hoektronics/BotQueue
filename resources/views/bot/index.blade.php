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
            <div class="col-md-3 table-bordered">
                {{ $bot->name }}
            </div>
            <div class="col-md-3 table-bordered">
                {{ $bot->status }}
            </div>
            <div class="col-md-6 table-bordered">
                @foreach($bot->clusters as $cluster)
                    <div class="button">
                        {{ $cluster->name }}
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
@endsection