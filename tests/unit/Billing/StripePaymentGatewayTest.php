<?php

use Stripe\Token;
use Stripe\Stripe;
use Stripe\Charge;
use App\Billing\StripePaymentGateway;

class StripePaymentGatewayTest extends TestCase
{
    private $lastCharge;

    protected function setUp()
    {
        parent::setUp();
        $this->lastCharge = $this->lastCharge();
    }
    /**
     * @test
     */
    function charges_with_a_valid_token_are_successful()
    {
        $paymentGateway = new StripePaymentGateway(config('services.stripe.secret'));

        $paymentGateway->charge(2500, $this->validToken());

        $this->assertCount(1, $this->newCharges());
        $this->assertEquals(2500, $this->lastCharge()->amount);
    }

    private function validToken()
    {
        return Token::create([
            'card' => [
                'number'    => '4242424242424242',
                'exp_month' => 1,
                'exp_year'  => date('Y') + 1,
                'cvc'       => "123",
            ],
        ],[
            'api_key' => config('services.stripe.secret')
        ])->id;
    }

    private function lastCharge()
    {
        return Charge::all(
            ['limit' => 1],
            ['api_key' => config('services.stripe.secret')]
        )['data'][0];
    }

    private function newCharges()
    {
        return Charge::all(
            [
                'ending_before' => $this->lastCharge->id,
            ],
            ['api_key' => config('services.stripe.secret')]
        )['data'];
    }
}
