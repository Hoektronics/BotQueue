@extends('layouts.app')

@section('content')
    <div class="flex mx-4">
        <div class="flex flex-col flex-grow mr-4">
            <span class="text-3xl">{{ $job->name }}</span>

            <div class="flex">
                @if($job->status == \App\Enums\JobStatusEnum::QUALITY_CHECK)
                    <form method="post" action="/jobs/{{$job->id}}/pass">
                        {{ csrf_field() }}
                        <input type="submit" value="Pass">
                    </form>

                    <form method="post" action="/jobs/{{$job->id}}/fail">
                        {{ csrf_field() }}
                        <input type="submit" value="Fail">
                    </form>
                @endif
            </div>
        </div>

        <div class="w-1/8">
            <div class="border rounded">
                <div class="text-center bg-gray-200">Info</div>
                <div class="p-4">
                    Creator: {{ $job->creator->username }}<br>
                    Worker:
                    @if(is_a($job->worker, App\Bot::class))
                        @inject('bot_status', 'App\Services\BotStatusService')

                        <a
                           href="{{ route('bots.show', [$job->worker]) }}">
                            {{ $job->worker->name }}
                        </a>
                    @else
                        <a class="hover:text-gray-700"
                           href="{{ route('clusters.show', [$job->worker]) }}">
                            {{ $job->worker->name }}
                        </a>
                    @endif
                    <br>

                    @if($job->status == \App\Enums\JobStatusEnum::IN_PROGRESS)
                        <div>
                            Progress: {{ number_format($job->progress, 2) }}%
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection