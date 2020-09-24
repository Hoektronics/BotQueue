<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function getListeners()
    {
        $id = Auth::user()->id;

        return [
            "echo-private:users.{$id},BotCreated" => "updateBots",
            "echo-private:users.{$id},BotDeleted" => "updateBots",
        ];
    }

    public function render()
    {
        return view('livewire.dashboard');
    }

    public function getBotsProperty()
    {
        return Auth::user()->bots;
    }

    public function updateBots()
    {
        $this->reset('bots');
    }
}
