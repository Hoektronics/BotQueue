<?php

use Faker\Generator as Faker;
use App\Bot;
use App\Job;
use App\Enums\JobStatusEnum;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Job::class, function (Faker $faker) {
    return [
        'name' => $faker->userName,
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

//$factory->state(Job::class, JobStatusEnum::ASSIGNED,
//    function () {
//        /** @var App\Bot $bot */
//        $bot = factory(Bot::class)->create([
//            'creator_id' => factory(App\User::class),
//        ]);
//
//        return [
//            'status' => JobStatusEnum::ASSIGNED,
//            'worker_id' => $bot->id,
//            'worker_type' => $bot->getMorphType(),
//            'bot_id' => $bot->id,
//        ];
//    });

//$factory->state(Job::class, JobStatusEnum::IN_PROGRESS,
//    function (Faker $faker, $attributes) {
//        /** @var App\Bot $bot */
//        $bot = factory(Bot::class)->create([
//            'creator_id' => $attributes['creator_id'],
//        ]);
//
//        return [
//            'status' => JobStatusEnum::IN_PROGRESS,
//            'worker_id' => $bot->id,
//            'worker_type' => $bot->getMorphType(),
//            'bot_id' => $bot->id,
//        ];
//    });