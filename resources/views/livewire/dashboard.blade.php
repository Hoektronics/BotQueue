<div>
    <div class="flex justify-between">
        <span class="text-3xl">Dashboard</span>
        <a role="button"
           href="{{ route('jobs.create') }}"
           class="btn-lg btn-blue btn-interactive">
            Start a job
        </a>
    </div>

    <div class="flex flex-wrap -mx-2">
        @forelse($this->bots as $bot)
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
</div>
