<?php
namespace Database\Factories;

use App\Concert;
use App\Order;
use App\Ticket;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(Concert::class, function (Faker\Generator $faker) {
    return [
        'title' => 'Example Band',
        'subtitle' => 'with The Fake Openers',
        'date' => Carbon::parse('+2 weeks'),
        'ticket_price' => 2000,
        'venue' => 'The Example Theater ',
        'venue_address' => '123 Example Lane',
        'city' => 'Fakeville',
        'state' => 'ON',
        'zip' => '90210',
        'additional_information' => 'Sample additional information',
    ];

});

$factory->state(Concert::class, 'published', function (Faker\Generator $faker) {
    return [
        'published_at' => Carbon::parse('-1 week'),
    ];
});

$factory->state(Concert::class, 'unpublished', function (Faker\Generator $faker) {
    return [
        'published_at' => null,
    ];
});


$factory->define(Ticket::class, function (Faker\Generator $faker) {
    return [
        'concert_id' => function () {
            return factory(Concert::class)->create()->id;
        }
    ];

});

$factory->state(Ticket::class, 'reserved', function (\Faker\Generator $faker) {
    return [
        'reserved_at' => Carbon::now()
    ];
});

$factory->define(Order::class, function (Faker\Generator $faker) {
    return [
        'amount' => '5250',
        'email' => 'test@test.com'
    ];

});