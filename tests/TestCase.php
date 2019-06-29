<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Helpers\UsesBuilders;
use Tests\Helpers\WithFakesEvents;

use Illuminate\Contracts\Bus\Dispatcher as BusDispatcherContract;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;
    use WithFakesEvents;
    use UsesBuilders;

    public function setUp()
    {
        parent::setUp();

        $this->withoutExceptionHandling();
    }

    public function withRemoteIp($ip)
    {
        return $this->withHeader('X-FORWARDED-FOR', $ip);
    }

    /*
     * We need this function until https://github.com/laravel/framework/pull/26437
     * is in our dependencies.
     */
    public function withoutJobs()
    {
        parent::withoutJobs();

        $this->app
            ->make(BusDispatcherContract::class)
            ->shouldIgnoreMissing();
    }

    /**
     * @param Model $model
     */
    public function assertDeleted($model)
    {
        $this->assertDatabaseMissing($model->getTable(), [
            $model->getKeyName() => $model->getKey(),
        ]);
    }
}
