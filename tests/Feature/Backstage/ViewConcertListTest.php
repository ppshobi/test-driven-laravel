<?php


namespace Tests\Feature\Backstage;


use App\User;
use App\Concert;
use Tests\TestCase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ViewConcertListTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp()
    {
        parent::setUp();
        TestResponse::macro('data', function ($key){
            return $this->original->getData()[$key];
        });
    }
    
    /**
     * @test
     **/
    function guests_cant_view_a_promoters_concerts_list()
    {
        $response = $this->get('/backstage/concerts');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * @test
     **/
    function promoter_can_only_view_a_list_of_their_own_concerts()
    {
        $this->disableExceptionHandling();
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $concertA =  factory(Concert::class)->create(['user_id' => $user->id]);
        $concertB =  factory(Concert::class)->create(['user_id' => $user->id]);
        $concertC =  factory(Concert::class)->create(['user_id' => $otherUser->id]);
        $concertD=  factory(Concert::class)->create(['user_id' => $user->id]);


        $response = $this->actingAs($user)->get('/backstage/concerts');

        $response->assertStatus(200);
        $this->assertTrue($response->data('concerts')->contains($concertA));
        $this->assertTrue($response->data('concerts')->contains($concertB));
        $this->assertTrue($response->data('concerts')->contains($concertD));
        $this->assertFalse($response->data('concerts')->contains($concertC));
    }
}