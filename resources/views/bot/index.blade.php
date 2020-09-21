@extends('layouts.app')

@section('content')
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
            @livewire('bot-card', ['botId' => $bot->id], key($bot->id))
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
@endsection