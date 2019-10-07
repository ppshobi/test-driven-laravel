<?php

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Concert;
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
    function customer_can_purchase_concert_tickets()
    {
        $this->disableExceptionHandling();
        $concert = factory(Concert::class)->create([
            'ticket_price' => 3250,
        ]);

        $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email'           => 'me@example.com',
            'ticket_quantity' => 3,
            'payment_token'  => $this->paymentGateway->getValidTestToken(),
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
//        $this->disableExceptionHandling();
        $concert = factory(Concert::class)->create();

        $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email'           => 'jane@example.com',
            'ticket_quantity' => 3,
            'purchase_token'  => 'invalid-token',
        ]);


        $this->assertResponseStatus(422);
        $order = $concert->orders()
            ->where('email', 'jane@example.com')
            ->first();

        $this->assertNull($order);

    }

}