<?php


namespace Tests;

use App;
use App\Host;

trait HasHost
{
    /** @var App\Host $host */
    protected $host;

    public function createTestHost()
    {
        $this->host = $this->createHost();
    }

    /**
     * @param array $overrides
     * @return Host
     */
    public function createHost($overrides = [])
    {
        $default = [
            'owner_id' => $this->user->id,
        ];

        return factory(Host::class)
            ->create(array_merge($default, $overrides));
    }
}
