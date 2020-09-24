<?php

namespace App\Http\Livewire;

use App\Enums\DriverType;
use App\Models\Bot;
use App\Services\BotDriverService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BotEditForm extends Component
{
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

        $driverService = app(BotDriverService::class);
        $driverService->decode($this->bot->driver);

        $this->driverType = $driverService->driver_type;
        $this->serialPort = $driverService->serial_port;
        $this->baudRate = $driverService->baud_rate;
        $this->commandDelay = $driverService->command_delay;
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
            DriverType::GCODE => "Gcode Driver",
            DriverType::DUMMY => "Dummy/Test Driver",
        ];
    }
}
