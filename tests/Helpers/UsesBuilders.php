<?php

namespace Tests\Helpers;

use App\Enums\JobStatusEnum;
use App\Enums\TaskStatusEnum;
use App\Enums\TaskType;
use App\Models\Host;
use App\Models\User;
use Faker\Generator as Faker;
use Tests\Helpers\Models\BotBuilder;
use Tests\Helpers\Models\ClusterBuilder;
use Tests\Helpers\Models\FileBuilder;
use Tests\Helpers\Models\HostBuilder;
use Tests\Helpers\Models\HostRequestBuilder;
use Tests\Helpers\Models\JobBuilder;
use Tests\Helpers\Models\TaskBuilder;
use Tests\Helpers\Models\UserBuilder;

/**
 * Trait UsesBuilders.
 * @property User mainUser
 * @property Host mainHost
 */
trait UsesBuilders
{
    /** @var User $lazyMainUser */
    private $lazyMainUser;
    /** @var Host $lazyMainHost */
    private $lazyMainHost;

    public function __get($name)
    {
        switch ($name) {
            case 'mainUser':
                if (! isset($this->lazyMainUser)) {
                    $this->lazyMainUser = $this->user()->create();

                    // By default, the first user on the site is admin
                    // For testing purposes, our main user shouldn't be
                    // an admin unless the test makes them an admin.
                    $this->lazyMainUser->is_admin = false;
                    $this->lazyMainUser->save();
                }

                return $this->lazyMainUser;
            case 'mainHost':
                if (! isset($this->lazyMainHost)) {
                    $this->lazyMainHost = $this->host()->create();
                }

                return $this->lazyMainHost;
        }
        throw new \Exception("Missing attribute $name");
    }

    public function user()
    {
        $faker = app(Faker::class);

        return (new UserBuilder())
            ->username($faker->name)
            ->email($faker->email)
            ->password($faker->password);
    }

    /**
     * @return ClusterBuilder
     */
    public function cluster()
    {
        $faker = app(Faker::class);

        return (new ClusterBuilder())
            ->creator($this->mainUser)
            ->name($faker->name);
    }

    /**
     * @return BotBuilder
     */
    public function bot()
    {
        $faker = app(Faker::class);

        return (new BotBuilder())
            ->creator($this->mainUser)
            ->cluster($this->cluster()->create())
            ->name($faker->name)
            ->type('3d_printer');
    }

    /**
     * @return FileBuilder
     */
    public function file()
    {
        $faker = app(Faker::class);

        return (new FileBuilder())
            ->uploader($this->mainUser)
            ->name($faker->name);
    }

    /**
     * @return JobBuilder
     */
    public function job()
    {
        $faker = app(Faker::class);

        return (new JobBuilder())
            ->creator($this->mainUser)
            ->state(JobStatusEnum::QUEUED)
            ->name($faker->name);
    }

    /**
     * @return HostBuilder
     */
    public function host()
    {
        $faker = app(Faker::class);

        return (new HostBuilder())
            ->creator($this->mainUser)
            ->name($faker->name);
    }

    /**
     * @param $taskType
     * @return TaskBuilder
     */
    public function task()
    {
        return (new TaskBuilder())
            ->type(TaskType::MAKE)
            ->status(TaskStatusEnum::READY)
            ->data('{}');
    }

    /**
     * @return HostRequestBuilder
     */
    public function hostRequest()
    {
        return new HostRequestBuilder();
    }
}
