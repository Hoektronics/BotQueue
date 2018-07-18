<?php

use Faker\Generator as Faker;
use App\Bot;
use App\Enums\BotStatusEnum;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Bot::class, function (Faker $faker) {
    return [
        'name' => $faker->userName,
        'seen_at' => $faker->dateTime,
        'type' => '3d_printer',
    ];
});

$factory->state(Bot::class, BotStatusEnum::OFFLINE, function () {
    return [
        'status' => BotStatusEnum::OFFLINE,
    ];
});

$factory->state(Bot::class, BotStatusEnum::IDLE, function () {
    return [
        'status' => BotStatusEnum::IDLE,
    ];
});

$factory->state(Bot::class, BotStatusEnum::WORKING, function () {
    return [
        'status' => BotStatusEnum::WORKING,
    ];
});

$factory->state(Bot::class, BotStatusEnum::PENDING, function () {
    return [
        'status' => BotStatusEnum::PENDING,
    ];
});