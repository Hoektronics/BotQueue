<?php


namespace Tests;

use App;
use App\Bot;
use Illuminate\Support\Facades\Auth;

trait HasBot
{
    /** @var Bot $bot */
    protected $bot;

    public function createTestBot()
    {
        $this->bot = $this->createBot();
    }

    /**
     * @param array $overrides
     * @return Bot
     */
    public function createBot($overrides = [])
    {
        $default = [
            'creator_id' => $this->user->id,
        ];

        return factory(App\Bot::class)->create(array_merge($default, $overrides));
    }
}
