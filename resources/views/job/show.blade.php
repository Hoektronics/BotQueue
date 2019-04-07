@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-9">
            <h1>{{ $job->name }}</h1>

            @if($job->status == \App\Enums\JobStatusEnum::QUALITY_CHECK)
                <form method="post" action="/jobs/{{$job->id}}/pass">
                    {{ csrf_field() }}
                    <input type="submit" value="Pass">
                </form>

                <form method="post" action="/jobs/{{$job->id}}/fail">
                    {{ csrf_field() }}
                    <input type="submit" value="Fail">
                </form>
            @else
                Main content
            @endif
        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    Info
                </div>
                <div class="card-body">
                    <div class="row">
                        Creator: {{ $job->creator->username }}
                    </div>

                    <div class="row">
                        Worker:
                        @if(is_a($job->worker, App\Bot::class))
                            @inject('bot_status', 'App\Services\BotStatusService')

                            <a class="badge {{ $bot_status->label_class($job->worker->status) }}"
                               href="{{ route('bots.show', [$job->worker]) }}">
                                {{ $job->worker->name }}
                            </a>
                        @else
                            <a class="badge badge-secondary"
                               href="{{ route('clusters.show', [$job->worker]) }}">
                                {{ $job->worker->name }}
                            </a>
                        @endif
                    </div>

                    @if($job->status == \App\Enums\JobStatusEnum::IN_PROGRESS)
                        <div class="row">
                            Progress: {{ number_format($job->progress, 2) }}%
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection