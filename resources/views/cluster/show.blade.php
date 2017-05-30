@extends('layouts.app')

@inject('bot_status', 'App\Services\BotStatusService')

@section('content')
    <div class="page-header">
        <h1>{{ $cluster->name }}</h1>
    </div>

    <div class="row">
        <div class="col-md-9">
            Main content
        </div>
        <div class="col-md-3">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Info
                </div>
                <div class="panel-body">
                    Creator: {{ $cluster->creator->username }}
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">
                    Bots
                </div>
                <div class="panel-body">
                    @foreach($cluster->bots as $bot)
                        <div class="row">
                            <div class="col-md-12">
                                <h4>
                                    <a class="label {{ $bot_status->label_class($bot->status) }}"
                                       href="{{ route('bot.show', [$bot]) }}">
                                        {{ $bot->name }}
                                    </a>
                                </h4>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
@endsection