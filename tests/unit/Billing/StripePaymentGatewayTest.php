<?php

use Stripe\Token;
use Stripe\Stripe;
use Stripe\Charge;
use App\Billing\StripePaymentGateway;
use App\Billing\PaymentFailedException;

/**
 * @group integration
 */
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
        $paymentGateway = $this->getPaymentGateway();
        $newCharges = $paymentGateway->newChargesDuring(function ($paymentGateway) {
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
        });

        $this->assertCount(1,$newCharges);
        $this->assertEquals(2500, $newCharges->sum());
    }

    protected function getPaymentGateway()
    {
        return new StripePaymentGateway(config('services.stripe.secret'));
    }

     /**
     * @test
     **/
    function charges_with_an_invalid_purchase_token_fails()
    {
        try {
            $paymentGateway = new StripePaymentGateway(config('services.stripe.secret'));
            $paymentGateway->charge(2500, 'invalid-token');
        } catch (PaymentFailedException $e) {
            $this->assertCount(0, $this->newCharges());
            return;
        }

        $this->fail("charging with an invalid payment token did not threw PaymentFailedException");
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
