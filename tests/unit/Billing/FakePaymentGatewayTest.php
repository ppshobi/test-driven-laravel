<?php

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;

class FakePaymentGatewayTest extends TestCase
{
    protected function getPaymentGateway()
    {
        return new FakePaymentGateway();
    }
    /**
     * @test
     */
    function charges_with_a_valid_token_are_successful()
    {
        $paymentGateway = $this->getPaymentGateway();
        $newCharges     = $paymentGateway->newChargesDuring(function ($paymentGateway) {
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
        });

        $this->assertCount(1, $newCharges);
        $this->assertEquals(2500, $newCharges->sum());
    }

    /**
     * @test
     **/
    function can_fetch_charges_created_during_a_callback()
    {
        $paymentGateway = $this->getPaymentGateway();
        $paymentGateway->charge(2000, $paymentGateway->getValidTestToken());
        $paymentGateway->charge(3000, $paymentGateway->getValidTestToken());

        $newCharges = $paymentGateway->newChargesDuring(function ($paymentGateway) {
            $paymentGateway->charge(4000, $paymentGateway->getValidTestToken());
            $paymentGateway->charge(5000, $paymentGateway->getValidTestToken());
        });

        $this->assertCount(2, $newCharges);
        $this->assertEquals([4000, 5000], $newCharges->all());
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
