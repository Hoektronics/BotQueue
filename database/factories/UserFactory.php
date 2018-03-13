<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */

$factory->define(App\User::class, function (Faker $faker) {
    // bcrypt of secret after 4 rounds
    static $password = '$2y$04$.3dfFzVmG17u2.SpTnYQn.wQ8Kc2DonYp8U7pquxstiB5RdNmFp.q';

    return [
        'username' => $faker->userName,
        'email' => $faker->unique()->safeEmail,
        'password' => $password,
        'remember_token' => str_random(10),
    ];
});

$factory->state(App\User::class, 'admin', [
    'is_admin' => true,
]);

$factory->define(App\HostRequest::class, function (Faker $faker) {
    return [
        'local_ip' => $faker->localIpv4,
        'remote_ip' => $faker->ipv4,
        'hostname' => $faker->domainWord,
    ];
});

$factory->define(App\Host::class, function (Faker $faker) {
    return [
        'local_ip' => $faker->localIpv4,
        'remote_ip' => $faker->ipv4,
        'name' => $faker->userName,
    ];
});
