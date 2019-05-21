@extends('layouts.app')

@inject('bot_status', 'App\Services\BotStatusService')

@section('content')
    <div class="flex mx-4">
        <div class="flex-grow mr-4">
            <span class="text-3xl">{{ $cluster->name }}</span>
        </div>

        <div class="w-1/8">
            <div class="border rounded mb-4">
                <div class="text-center bg-gray-200">Info</div>
                <div class="p-4">
                    Creator: {{ $cluster->creator->username }}
                </div>
            </div>

            <div class="border rounded mb-4">
                <div class="text-center bg-gray-200">Bots</div>
                <div class="flex flex-col p-4">
                    @forelse($cluster->bots as $bot)
                            <a href="{{ route('bots.show', [$bot]) }}"
                            class="{{ $bot_status->label_class($bot->status) }} w-full text-center mt-1 mb-1">
                                {{ $bot->name }}
                            </a>
                    @empty
                        No bots found.
                        <a href="{{ route('bots.create') }}"
                        class="hover:text-gray-700">
                            Create one?
                        </a>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
@endsection