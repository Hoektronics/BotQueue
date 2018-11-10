<?php

use Faker\Generator as Faker;
use App\Bot;
use App\Cluster;
use App\Job;
use App\Enums\JobStatusEnum;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Job::class, function (Faker $faker) {
    return [
        'name' => $faker->userName,
    ];
});

$factory->state(Job::class, 'worker:cluster', function () {
    return [
        'worker_id' => function ($attributes) {
            return factory(Cluster::class)->create([
                'creator_id' => $attributes['creator_id']
            ]);
        },
        'worker_type' => function ($attributes) {
            return Cluster::query()->find($attributes['worker_id'])->getMorphClass();
        }
    ];
});

$factory->state(Job::class, JobStatusEnum::QUEUED, function () {
    return [
        'status' => JobStatusEnum::QUEUED,
        'worker_id' => function ($attributes) {
            return factory(Bot::class)->create([
                'creator_id' => $attributes['creator_id']
            ]);
        },
        'worker_type' => function ($attributes) {
            return Bot::query()->find($attributes['worker_id'])->getMorphClass();
        }
    ];
});

$factory->state(Job::class, JobStatusEnum::ASSIGNED,
    function () {
        return [
            'status' => JobStatusEnum::ASSIGNED,
            'bot_id' => function ($attributes) {
                return factory(Bot::class)->create([
                    'creator_id' => $attributes['creator_id']
                ]);
            },
            'worker_id' => function ($attributes) {
                return $attributes['bot_id'];
            },
            'worker_type' => function ($attributes) {
                return Bot::query()->find($attributes['worker_id'])->getMorphClass();
            },
        ];
    });

$factory->state(Job::class, JobStatusEnum::IN_PROGRESS,
    function (Faker $faker, $attributes) {
        /** @var App\Bot $bot */
        $bot = factory(Bot::class)->create([
            'creator_id' => $attributes['creator_id'],
        ]);

        return [
            'status' => JobStatusEnum::IN_PROGRESS,
            'worker_id' => $bot->id,
            'worker_type' => $bot->getMorphClass(),
            'bot_id' => $bot->id,
        ];
    });

$factory->state(Job::class, JobStatusEnum::QUALITY_CHECK,
    function (Faker $faker, $attributes) {
        /** @var App\Bot $bot */
        $bot = factory(Bot::class)->create([
            'creator_id' => $attributes['creator_id'],
        ]);

        return [
            'status' => JobStatusEnum::QUALITY_CHECK,
            'worker_id' => $bot->id,
            'worker_type' => $bot->getMorphClass(),
            'bot_id' => $bot->id,
        ];
    });

$factory->state(Job::class, JobStatusEnum::COMPLETED,
    function (Faker $faker, $attributes) {
        /** @var App\Bot $bot */
        $bot = factory(Bot::class)->create([
            'creator_id' => $attributes['creator_id'],
        ]);

        return [
            'status' => JobStatusEnum::COMPLETED,
            'worker_id' => $bot->id,
            'worker_type' => $bot->getMorphClass(),
            'bot_id' => $bot->id,
        ];
    });

$factory->state(Job::class, JobStatusEnum::FAILED,
    function (Faker $faker, $attributes) {
        /** @var App\Bot $bot */
        $bot = factory(Bot::class)->create([
            'creator_id' => $attributes['creator_id'],
        ]);

        return [
            'status' => JobStatusEnum::FAILED,
            'worker_id' => $bot->id,
            'worker_type' => $bot->getMorphClass(),
            'bot_id' => $bot->id,
        ];
    });

$factory->state(Job::class, JobStatusEnum::CANCELLED,
    function (Faker $faker, $attributes) {
        /** @var App\Bot $bot */
        $bot = factory(Bot::class)->create([
            'creator_id' => $attributes['creator_id'],
        ]);

        return [
            'status' => JobStatusEnum::CANCELLED,
            'worker_id' => $bot->id,
            'worker_type' => $bot->getMorphClass(),
            'bot_id' => $bot->id,
        ];
    });