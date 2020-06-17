<?php

namespace Tests;

use Illuminate\Contracts\Bus\Dispatcher as BusDispatcherContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Helpers\UsesBuilders;
use Tests\Helpers\WithFakesEvents;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;
    use WithFakesEvents;
    use UsesBuilders;

    protected function setUp(): void
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
