@extends('layouts.app')

@inject('bot_status', 'App\Services\BotStatusService')

@section('content')
    <div class="page-header">
        <div class="btn-toolbar pull-right">
            <a role="button" class="btn btn-primary btn-lg" href="/bot/create">Create a Bot</a>
        </div>
        <h1>Bots</h1>
    </div>

    @if(count($bots) == 0)
        <h4>
            Hello! This is the page where you can see all of your bots, but it looks like you don't have any defined.
        </h4>
        <h4>
            Click the "Create a Bot" button to make one
        </h4>
    @else
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="col-md-3">Bot Name</th>
                    <th class="col-md-1">Status</th>
                    <th>Clusters</th>
                </tr>
            </thead>
            @foreach($bots as $bot)
                <tr>
                    <th>
                        <a href="{{ route('bot.show', [$bot]) }}">{{ $bot->name }}</a>
                    </th>
                    <th>
                        {!! $bot_status->label($bot->status) !!}
                    </th>
                    <th>
                        @foreach($bot->clusters as $cluster)
                            <a class="label label-info"
                               href="{{ route('cluster.show', [$cluster]) }}">
                                {{ $cluster->name }}
                            </a>
                        @endforeach
                    </th>
                </tr>
            @endforeach
        </table>
    @endif
@endsection