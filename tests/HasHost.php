<?php


namespace Tests;

use App;
use Illuminate\Support\Facades\Auth;

trait HasHost
{
    /** @var App\Host $host */
    protected $host;

    public function createTestHost()
    {
        $this->host = factory(App\Host::class)->create([
            'owner_id' => $this->user->id,
        ]);
    }
}
