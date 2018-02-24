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
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    Clusters
                </div>
                <div class="card-body">
                    @foreach($bot->clusters as $cluster)
                        <div class="row">
                            <h4>
                                <a class="badge badge-secondary"
                                   href="{{ route('clusters.show', [$cluster]) }}">
                                    {{ $cluster->name }}
                                </a>
                            </h4>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
@endsection