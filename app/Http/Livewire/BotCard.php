<?php

namespace App\Http\Livewire;

use App\Actions\BringBotOnline;
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
    public $bot;

    public function render()
    {
        return view('livewire.bot-card');
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
    }
}
