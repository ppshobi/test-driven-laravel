<?php

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FakePaymentGatewayTest extends TestCase
{
    /**
     * @test
     */
    function charges_with_a_valid_token_are_successful()
    {
        $paymentGateway = $this->getPaymentGateway();
        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

        $this->assertEquals(2500, $paymentGateway->totalCharges());
    }


    protected function getPaymentGateway()
    {
        return new FakePaymentGateway();
    }


    /**
     * @test
     **/
    function charges_with_an_invalid_purchase_token_fails()
    {
        try {
            $paymentGateway = new FakePaymentGateway;
            $paymentGateway->charge(2500, 'invalid-token');
        } catch (PaymentFailedException $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @test
     **/
    function running_a_hook_before_first_charge()
    {
        $paymentGateway = new FakePaymentGateway;

        $timesCallBackRan = 0;
        $paymentGateway->beforeFirstCharge(function ($paymentGateway) use (&$timesCallBackRan) {
            $timesCallBackRan++;
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
            $this->assertEquals(2500, $paymentGateway->totalCharges());
        });

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
        $this->assertEquals(1, $timesCallBackRan);
        $this->assertEquals(5000, $paymentGateway->totalCharges());
    }
}
