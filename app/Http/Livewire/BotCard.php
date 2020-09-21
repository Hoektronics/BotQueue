<?php

namespace App\Http\Livewire;

use App\Actions\BringBotOnline;
use App\Actions\TakeBotOffline;
use App\Enums\BotStatusEnum;
use App\Exceptions\BotStatusConflict;
use App\Models\Bot;
use Illuminate\Support\Arr;
use Livewire\Component;

class BotCard extends Component
{
    protected $statusToColors = [
        BotStatusEnum::OFFLINE => 'bg-black text-white',
        BotStatusEnum::JOB_ASSIGNED => 'bg-gray-600 text-white',
        BotStatusEnum::IDLE => 'bg-green-500 text-white',
        BotStatusEnum::WORKING => 'bg-blue-400 text-white',
        BotStatusEnum::WAITING => 'bg-gray-600 text-white',
    ];
    
    /**
     * @var Bot
     */
    protected $bot;
    public $botId;

    public function mount()
    {
        $this->bot = Bot::find($this->botId);
    }

    public function hydrate()
    {
        $this->bot = Bot::find($this->botId);
    }

    public function render()
    {
        return view('livewire.bot-card');
    }

    public function getBotProperty()
    {
        return $this->bot;
    }

    public function getStatusProperty()
    {
        return ucwords(str_replace('_', ' ', $this->bot->status));
    }

    public function getStatusColorProperty()
    {
        if(Arr::exists($this->statusToColors, $this->bot->status)) {
            return $this->statusToColors[$this->bot->status];
        }

        return 'bg-white text-black';
    }

    public function getMenuItemsProperty()
    {
        switch ($this->bot->status) {
            case BotStatusEnum::OFFLINE:
                return [
                    "Bring Online" => "bringBotOnline",
                    "Edit Bot" => "editBot",
                ];
            case BotStatusEnum::IDLE:
                return [
                    "Take Offline" => "takeBotOffline",
                    "Edit Bot" => "editBot",
                ];
            default:
                return [];
        }
    }

    /**
     * @throws BotStatusConflict
     */
    public function bringBotOnline()
    {
        app(BringBotOnline::class)->execute($this->bot);
        $this->closeMenu();
    }

    /**
     * @throws BotStatusConflict
     */
    public function takeBotOffline()
    {
        app(TakeBotOffline::class)->execute($this->bot);
        $this->closeMenu();
    }

    public function editBot()
    {
        return redirect()->route('bots.edit', [$this->bot]);
    }

    private function closeMenu()
    {
        $this->dispatchBrowserEvent('menu-item-clicked');
    }
}
