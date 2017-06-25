@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h1>{{ $job->name }}</h1>
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
                    <div class="row">
                        Creator: {{ $job->creator->username }}
                    </div>
                    <div class="row">
                        Worker:
                        @if(is_a($job->worker, App\Job::class))
                            @inject('bot_status', 'App\Services\BotStatusService')

                            <a class="label {{ $bot_status->label_class($job->worker->status) }}"
                               href="{{ route('bots.show', [$job->worker]) }}">
                                {{ $job->worker->name }}
                            </a>
                        @else
                            <a class="label label-default"
                               href="{{ route('clusters.show', [$job->worker]) }}">
                                {{ $job->worker->name }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection