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

        @if(count($bots) == 0)
            <h4>
                Hello! This is the page where you can see all of your bots, but it looks like you don't have any
                defined.
            </h4>
            <h4>
                Click the "Create a Bot" button to make one
            </h4>
        @else
            <div class="mt-4 flex flex-col w-full md:w-1/2 md:mx-auto">
                <div class="flex w-full">
                    <a class="w-1/3 text-center text-xl p-1 border-l border-t border-b rounded-tl">Bot Name</a>
                    <a class="w-1/3 text-center text-xl p-1 border-t border-b">Status</a>
                    <a class="w-1/3 text-center text-xl p-1 border-r border-t border-b rounded-tr">Clusters</a>
                </div>
                @foreach($bots as $bot)
                    <div class="flex w-full p-2 border-l border-r border-b">
                        <div class="w-1/3 text-center hover:text-gray-700">
                            <a href="{{ route('bots.show', [$bot]) }}">{{ $bot->name }}</a>
                        </div>
                        <div class="w-1/3 text-center">
                            {!! $bot_status->label($bot->status) !!}
                        </div>
                        <div class="w-1/3 text-center hover:text-gray-700">
                            @if($bot->cluster !== null)
                                <a
                                        href="{{ route('clusters.show', [$bot->cluster]) }}">
                                    {{ $bot->cluster->name }}
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection