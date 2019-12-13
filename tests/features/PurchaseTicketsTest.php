<?php

use App\Concert;
use App\Facades\TicketCode;
use App\TicketCodeGenerator;
use App\Billing\PaymentGateway;
use App\Billing\FakePaymentGateway;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmationEmail;
use App\Facades\OrderConfirmationNumber;
use App\OrderConfirmationNumberGenerator;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PurchaseTicketsTest extends TestCase
{
    use DatabaseMigrations;

    protected $paymentGateway;

    protected function setUp()
    {
        parent::setUp();
        Mail::fake();
        $this->paymentGateway = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
    }

    /**
     * @test
     */
    function customer_can_purchase_tickets_for_published_concert()
    {
        $this->disableExceptionHandling();

        OrderConfirmationNumber::shouldReceive('generate')->andReturn('ORDERCONFIRMATION1234');
        TicketCode::shouldReceive('generateFor')->andReturn('TICKETCODE1','TICKETCODE2','TICKETCODE3');
        $concert = factory(Concert::class)->create([
            'ticket_price' => 3250,
        ])->addTickets(3);

        $this->orderTickets($concert, [
            'email'           => 'me@example.com',
            'ticket_quantity' => 3,
            'purchase_token'  => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(201);

        $this->seeJsonSubset([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'email'           => 'me@example.com',
            'amount'          => 9750,
            'tickets' => [
                ['code' => 'TICKETCODE1'],
                ['code' => 'TICKETCODE2'],
                ['code' => 'TICKETCODE3'],
            ]
        ]);

        $this->assertEquals(9750, $this->paymentGateway->totalCharges());
        $this->assertTrue($concert->hasOrderFor('me@example.com'));
        $order =  $concert->ordersFor('me@example.com')->first();
        $this->assertEquals(3, $order->ticketQuantity());
        Mail::assertSent(OrderConfirmationEmail::class, function($mail) use($order) {
            return $mail->hasTo('me@example.com') && $mail->order->id == $order->id;
        });
    }

    /**
     * @test
     **/
    function cannot_purchase_tickets_for_unpublished_concerts()
    {
        $concert = factory(Concert::class)->states('unpublished')
            ->create()
            ->addTickets(3);

        $this->orderTickets($concert, [
            'email'           => 'me@example.com',
            'ticket_quantity' => 3,
            'purchase_token'  => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(404);
        $this->assertFalse($concert->hasOrderFor('me@example.com'));
        $this->assertEquals(0, $this->paymentGateway->totalCharges());

    }


    /**
     * @test
     **/
    function cant_purchase_tickets_another_customer_is_already_trying_to_purchase()
    {
        $this->disableExceptionHandling();
        $concert = factory(Concert::class)
            ->create(['ticket_price' => 1200])
            ->addTickets(3);

        $this->paymentGateway->beforeFirstCharge(function ($paymentGateway) use ($concert) {

            $this->orderTickets($concert, [
                'email'           => 'personB@example.com',
                'ticket_quantity' => 1,
                'purchase_token'  => $paymentGateway->getValidTestToken(),
            ]);
            $this->assertResponseStatus(422);
            $this->assertFalse($concert->hasOrderFor('personB@example.com'));
            $this->assertEquals(0, $paymentGateway->totalCharges());
        });

        $this->orderTickets($concert, [
            'email'           => 'personA@example.com',
            'ticket_quantity' => 3,
            'purchase_token'  => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertEquals(3600, $this->paymentGateway->totalCharges());
        $this->assertTrue($concert->hasOrderFor('personA@example.com'));
        $this->assertEquals(3, $concert->ordersFor('personA@example.com')->first()->ticketQuantity());
    }

    function orderTickets($concert, $customer)
    {
        $savedRequest = $this->app['request'];

        $this->response = $this->json('POST', "/concerts/{$concert->id}/orders", $customer);

        $this->app['request'] = $savedRequest;
    }

    /**
     * @test
     **/
    function email_is_required_for_ordering_tickets()
    {
        $concert = factory(Concert::class)->create();

        $this->orderTickets($concert, [
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
        $concert = factory(Concert::class)->create()->addTickets(3);
        $this->orderTickets($concert, [
            'email'           => 'jane@example.com',
            'ticket_quantity' => 3,
            'purchase_token'  => 'invalid-payment-token',
        ]);

        $this->assertResponseStatus(422);

        $this->assertFalse($concert->hasOrderFor('jane@example.com'));
        $this->assertEquals(3, $concert->ticketsRemaining());
    }

    /**
     * @test
     **/
    function cannot_purchase_more_tickets_than_remaining()
    {
        $concert = factory(Concert::class)->create()->addTickets(50);

        $this->orderTickets($concert, [
            'email'           => 'me@example.com',
            'ticket_quantity' => 51,
            'purchase_token'  => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(422);
        $this->assertFalse($concert->hasOrderFor('me@example.com'));
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    private function assertResponseStatus($status)
    {
        $this->response->assertStatus($status);
    }
    private function seeJsonSubset($data)
    {
        $this->response->assertJson($data);
    }
    private function decodeResponseJson()
    {
        return $this->response->decodeResponseJson();
    }
}