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

$factory->define(App\Purchase::class, function (Faker $faker) {
    return [
        'contract_id' => 1,
        'datetime' => '2019-24-01',
        'credits_spent' => 10,
    ];
});
