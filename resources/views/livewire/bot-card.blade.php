<div class="w-full pt-4 px-2 md:w-1/3">
    <div class="border rounded-t shadow-md">
        <div class="p-2 flex text-lg bg-gray-300 items-center">
            <a href="{{ route('bots.show', [$bot]) }}"
               class="text-lg flex-grow mr-2">
                {{ $bot->name }}
            </a>

            <div class="relative">
                <button class="relative rounded-full shadow whitespace-no-wrap flex fill-current items-center p-1 pl-4 pr-2 cursor-pointer z-10 {{ $this->status_color }}">
                    {{ $this->status }}
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="h-6">
                        <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                    </svg>
                </button>
                @if(count($this->menu_items) > 0)
                    <div class="mt-2 py-2 w-48 absolute bg-white border shadow-xl rounded-lg">
                        @foreach($this->menu_items as $menu_title => $action)
                            <a href="#"
                               wire:click="{{ $action }}"
                               class="block px-2 hover:bg-blue-500 hover:text-white">
                                {{ $menu_title }}
                            </a>
                        @endforeach
                    </div>
                @endif
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
