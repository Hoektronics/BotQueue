<?php

namespace Tests;

use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Models\Bot;
use App\Models\Job;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcherContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Helpers\TestStatus;
use Tests\Helpers\TestStatusException;
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
    public function assertModelDeleted($model)
    {
        $this->assertDatabaseMissing($model->getTable(), [
            $model->getKeyName() => $model->getKey(),
        ]);
    }

    public function exceptStatus(string ...$status)
    {
        $data = array_filter(array_values($this->getProvidedData()),
            function ($parameter) {
                return is_a($parameter, TestStatus::class);
            });

        if(count($data) == 0) {
            return;
        }

        /** @var TestStatus $testStatus */
        $testStatus = reset($data);

        if(in_array((string) $testStatus, $status)) {
            /**
             * We're going to skip this status, so we throw an exception to get out of the test early.
             * We also want to not fail (or skip) the test, so we expect the exception.
             */
            $this->expectException(TestStatusException::class);
            throw new TestStatusException($testStatus);
        }
    }

    public static function botStates()
    {
        return BotStatusEnum::allStates()
            ->reduce(function ($lookup, $item) {
                $lookup[$item] = [new TestStatus($item)];

                return $lookup;
            }, []);
    }

    public static function jobStates()
    {
        return JobStatusEnum::allStates()
            ->reduce(function ($lookup, $item) {
                $lookup[$item] = [new TestStatus($item)];

                return $lookup;
            }, []);
    }

    /**
     * Using an update this way means hydrated models still have the old data, but the DB is different.
     * This is useful for testing race conditions when another process changes the data from under you.
     *
     * @param Model $model
     * @param $fields
     */
    public function updateModelDb(Model $model, $fields)
    {
        $model->newQuery()
            ->whereKey($model->getKey())
            ->update($fields);
    }
}
