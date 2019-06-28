@extends('layouts.app')

@inject('bot_status', 'App\Services\BotStatusService')

@section('content')
    <div class="mx-4">
        <div class="flex justify-between">
            <span class="text-3xl">Bots</span>
            <a role="button"
               href="{{ route('bots.create') }}"
               class="btn-lg btn-blue btn-interactive">
                Create a Bot
            </a>
        </div>

        <div class="flex flex-wrap -mx-2">
            @forelse($bots as $bot)
                <div class="w-full pt-4 px-2 md:w-1/3">
                    <div class="border rounded-t">
                        <div class="flex p-2 text-lg bg-gray-300">
                            <a href="{{ route('bots.show', [$bot]) }}"
                                    class="text-lg flex-grow">
                                {{ $bot->name }}
                            </a>

                            <div>
                                {!! $bot_status->label($bot->status) !!}
                            </div>
                        </div>

                        <div class="p-2">
                            @if($bot->currentJob)
                                Job: <a href="{{ route('jobs.show', [$bot->currentJob]) }}">
                                    {{ $bot->currentJob->name }}
                                </a>
                            @else
                                Job: None
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <h4>
                    Hello! This is the page where you can see all of your bots, but it looks like you don't have any
                    defined.
                </h4>
                <h4>
                    Click the "Create a Bot" button to make one
                </h4>
            @endforelse
        </div>
    </div>
@endsection