@extends('layouts.app')

@inject('bot_status', 'App\Services\BotStatusService')

@section('content')
    <div class="flex items-start mx-4">
        <div class="flex-col flex-grow mr-4">
            <div class="flex">
                <span class="text-3xl mr-2">{{ $bot->name }}</span>
                <a href="{{ route('bots.edit', [$bot]) }}"
                   class="my-auto text-gray-500 hover:text-blue-500 mx-1">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                         class="h-6 fill-current">
                        <path d="M12.3 3.7l4 4L4 20H0v-4L12.3 3.7zm1.4-1.4L16 0l4 4-2.3 2.3-4-4z"></path>
                    </svg>
                </a>
                <a href="{{ route('bots.delete', [$bot]) }}"
                   class="my-auto text-gray-500 hover:text-red-500 mx-1">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                         class="h-6 fill-current">
                        <path d="M6 2l2-2h4l2 2h4v2H2V2h4zM3 6h14l-1 14H4L3 6zm5 2v10h1V8H8zm3 0v10h1V8h-1z"></path>
                    </svg>
                </a>
            </div>
        </div>

        <div class="w-1/8">
            <div class="border rounded">
                <div class="text-center bg-gray-200">Info</div>
                <div class="p-4">
                    <ul>
                        <li>
                            Creator: {{ $bot->creator->username }}
                        </li>
                        <li>
                            Status: {!! $bot_status->label($bot->status) !!}<br>
                        </li>
                        @if($bot->cluster !== null)
                            <li>
                                Cluster:
                                <a class="hover:text-gray-700"
                                   href="{{ route('clusters.show', [$bot->cluster]) }}">
                                    {{ $bot->cluster->name }}
                                </a>
                            </li>
                        @endif
                        @if($bot->host !== null)
                            <li>
                                Host:
                                <a class="hover:text-gray-700">
                                {{ $bot->host->name }}
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

        </div>
    </div>
@endsection