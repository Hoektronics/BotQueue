<?php

namespace App\Http\Livewire;

use App\Models\Bot;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BotEditForm extends Component
{
    const GCODE_DRIVER = 'gcode';
    const DUMMY_DRIVER = 'dummy';

    /**
     * @var Bot
     */
    protected $bot;
    public $botId;
    public $botName;
    public $hostId;

    public $driverType;
    public $serialPort;
    public $baudRate;
    public $commandDelay;

    public function mount()
    {
        $this->bot = Bot::find($this->botId);
        $this->botName = $this->bot->name;
        $this->hostId = $this->bot->host_id;

        // TODO Get and decode JSON object to find correct driver
        $this->driverType = self::GCODE_DRIVER;
    }

    public function hydrate()
    {
        $this->bot = Bot::find($this->botId);
        $this->botName = $this->bot->name;
    }

    public function render()
    {
        return view('livewire.bot-edit-form');
    }

    public function getBotProperty()
    {
        return $this->bot;
    }

    public function getHostsProperty()
    {
        return Auth::user()->hosts;
    }

    public function getDriversProperty()
    {
        return [
            self::GCODE_DRIVER => "Gcode Driver",
            self::DUMMY_DRIVER => "Dummy/Test Driver",
        ];
    }
}
