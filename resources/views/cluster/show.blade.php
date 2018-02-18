@extends('layouts.app')

@inject('bot_status', 'App\Services\BotStatusService')

@section('content')
    <div class="row">
        <div class="col-md-9">
            <h1>{{ $cluster->name }}</h1>

            Main content
        </div>

        <div class="col-md-3">
            <div class="card mb-3">
                <div class="card-header">
                    Info
                </div>
                <div class="card-body">
                    Creator: {{ $cluster->creator->username }}
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    Bots
                </div>
                <div class="card-body">
                    @foreach($cluster->bots as $bot)
                        <div class="row">
                            <h4>
                                <a class="badge {{ $bot_status->label_class($bot->status) }}"
                                   href="{{ route('bots.show', [$bot]) }}">
                                    {{ $bot->name }}
                                </a>
                            </h4>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
@endsection