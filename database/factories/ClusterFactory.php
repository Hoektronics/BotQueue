<?php

use Faker\Generator as Faker;
use App\Cluster;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Cluster::class, function (Faker $faker) {
    return [
        'name' => $faker->userName,
    ];
});