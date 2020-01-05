<?php
namespace Tests\Features;

use App\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\BrowserKitTestCase;

class ViewConcertListingTest extends BrowserKitTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function user_can_view_a_published_concert_listing()
    {
        //Arrange
        //Create a concert
        $concert = factory(Concert::class)->states(['published'])
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
        $this->visit('/concerts/' . $concert->id);


        //Assert
        //See the concert details

        $this->see('The great Cord');
        $this->see('with Animosity and Lethargy');
        $this->see('December 13, 2016');
        $this->see('8:00PM');
        $this->see('32.50');
        $this->see('The mosh pit');
        $this->see('123 Example Lane');
        $this->see('Laraville, ON 17916');
        $this->see('info');
    }

    /**
     * @test
     */
    public function user_cannot_view_unpublished_concert_listings()
    {
        $this->withoutExceptionHandling();
        $concert = factory(Concert::class)->states(['unpublished'])->create();

        $this->get('/concerts/' . $concert->id);

        $this->assertResponseStatus(404);
    }
}
