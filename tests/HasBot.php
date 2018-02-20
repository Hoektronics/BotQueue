<?php


namespace Tests;

use App;
use Illuminate\Support\Facades\Auth;

trait HasBot
{
    /** @var App\Bot $bot */
    protected $bot;

    public function createTestBot()
    {
        $this->bot = factory(App\Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);
    }
}
