@extends('layouts.app')

@inject('bot_status', 'App\Services\BotStatusService')

@section('content')
    <div class="flex mx-4">
        <div class="flex-grow">
            <span class="text-3xl">{{ $bot->name }}</span>
        </div>
        <div class="flex-1/8">
            <div class="border rounded">
                <div class="text-center bg-gray-200">Info</div>
                <div class="p-4">
                    Creator: {{ $bot->creator->username }}<br>
                    Status: {!! $bot_status->label($bot->status) !!}<br>
                    @if($bot->cluster !== null)
                        Cluster:
                        <a class="hover:text-gray-700"
                           href="{{ route('clusters.show', [$bot->cluster]) }}">
                            {{ $bot->cluster->name }}
                        </a>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection