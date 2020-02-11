<?php


namespace Tests\Feature\Backstage;


use Tests\TestCase;

class ViewConcertListTest extends TestCase
{
    /**
     * @test
     **/
    function guests_cant_view_a_promoters_concerts_list()
    {
        $response = $this->get('/backstage/concerts');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
}