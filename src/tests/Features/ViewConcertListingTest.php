<?php

namespace Tests\Features;

use App\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
/**
 * @group Feature
 */
class ViewConcertListingTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function user_can_view_a_published_concert_listing()
    {
        //Arrange
        //Create a concert
        $concert = Concert::factory()->published()
            ->create([
                'title' => 'The great Cord',
                'subtitle' => 'with Animosity and Lethargy',
                'date' =>
                    Carbon::parse('December 13, 2016 8PM'),
                'ticket_price' => 3250,
                'venue' => 'The mosh pit',
                'venue_address' => '123 Example Lane',
                'city' => 'Laraville',
                'state' => 'ON',
                'zip' => '17916',
                'additional_information' => 'info',
            ]);

        //Act
        //View the concert listing
        $response = $this->get('/concerts/' . $concert->id);


        //Assert
        //See the concert details
        $response->assertOk();
        $response->assertSee('The great Cord');
        $response->assertSee('with Animosity and Lethargy');
        $response->assertSee('December 13, 2016');
        $response->assertSee('8:00pm');
        $response->assertSee('32.50');
        $response->assertSee('The mosh pit');
        $response->assertSee('123 Example Lane');
        $response->assertSee('Laraville, ON 17916');
        $response->assertSee('info');
    }

    /** @test */
    public function user_cannot_view_unpublished_concert_listings()
    {
        $this->withoutExceptionHandling();
        $concert = Concert::factory()->unpublished()->create();

        $response = $this->get('/concerts/' . $concert->id);

        $response->assertStatus(404);
    }
}
