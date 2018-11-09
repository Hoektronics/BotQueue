@extends('layouts.app')

@inject('bot_status', 'App\Services\BotStatusService')

@section('content')
    <div class="row">
        <div class="col-md-9">
            <h1>{{ $bot->name }}</h1>

            Main content
        </div>
        <div class="col-md-3">
            <div class="card mb-3">
                <div class="card-header">
                    Info
                </div>
                <div class="card-body">
                    Creator: {{ $bot->creator->username }}<br>
                    Status: {!! $bot_status->label($bot->status) !!}
                    @if($bot->cluster !== null)
                        <a class="badge badge-secondary"
                           href="{{ route('clusters.show', [$bot->cluster]) }}">
                            {{ $bot->cluster->name }}
                    </a>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection