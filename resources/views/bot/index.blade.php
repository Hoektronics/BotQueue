@extends('layouts.app')

@inject('bot_status', 'App\Services\BotStatusService')

@section('content')
    <div class="page-header">
        <div class="btn-toolbar pull-right">
            <a role="button" class="btn btn-primary btn-lg" href="/bot/create">Create a Bot</a>
        </div>
        <h1>Bots</h1>
    </div>

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
@endsection