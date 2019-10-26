<?php

use App\Concert;
use App\Billing\PaymentGateway;
use App\Billing\FakePaymentGateway;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PurchaseTicketsTest extends TestCase
{
    use DatabaseMigrations;

    protected $paymentGateway;
    protected function setUp()
    {
        parent::setUp();

        $this->paymentGateway = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
    }
    /**
     * @test
     */
    function customer_can_purchase_tickets_for_published_concert()
    {
        $this->disableExceptionHandling();
        $concert = factory(Concert::class)->create([
            'ticket_price' => 3250,
        ]);

        $concert->addTickets(3);

        $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email'           => 'me@example.com',
            'ticket_quantity' => 3,
            'purchase_token'  => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(201);
        $this->assertEquals(9750, $this->paymentGateway->totalCharges());

        $order = $concert->orders()
            ->where('email', 'me@example.com')
            ->first();

        $this->assertNotNull($order);

        $this->assertCount(3, $order->tickets);
    }

    /**
     * @test
     **/
    function cannot_purchase_tickets_for_unpublished_concerts()
    {
        $concert = factory(Concert::class)->states('unpublished')->create();
        $concert->addTickets(3);

        $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email'           => 'me@example.com',
            'ticket_quantity' => 3,
            'purchase_token'  => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(404);
        $this->assertEquals(0, $concert->orders()->count());
         $this->assertEquals(0, $this->paymentGateway->totalCharges());

    }

    /**
     * @test
     **/
    function email_is_required_for_ordering_tickets()
    {
        $concert = factory(Concert::class)->create();

        $this->json('POST', "/concerts/{$concert->id}/orders", [
            'ticket_quantity' => 3,
            'purchase_token'  => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(422);

        $this->assertArrayHasKey('email', $this->decodeResponseJson());
    }

    /**
     * @test
     **/
    function order_is_not_created_if_payment_fails()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(3);
        $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email'           => 'jane@example.com',
            'ticket_quantity' => 3,
            'purchase_token'  => 'invalid-payment-token',
        ]);

        $this->assertResponseStatus(422);
        $order = $concert->orders()
            ->where('email', 'jane@example.com')
            ->first();

        $this->assertNull($order);

    }

    /**
     * @test
     **/
    function cannot_purchase_more_tickets_than_remaining()
    {
        $concert = factory(Concert::class)->create();

        $concert->addTickets(50);

        $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email'           => 'me@example.com',
            'ticket_quantity' => 51,
            'purchase_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(422);

        $order = $concert->orders()
            ->where('email', 'me@example.com')
            ->first();
        $this->assertNull($order);

        $this->assertEquals(0, $this->paymentGateway->totalCharges());

        $this->assertEquals(50, $concert->ticketsRemaining());
    }

}