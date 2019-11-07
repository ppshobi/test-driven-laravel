<?php

namespace App\Billing;

use Stripe\Token;
use Stripe\Charge;
use Stripe\Exception\InvalidRequestException;

class StripePaymentGateway implements PaymentGateway
{

    private $apiKey;
    /**
     * StripePaymentGateway constructor.
     */
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function charge($amount, $token)
    {
        try {
            Charge::create([
                'amount'   => $amount,
                'source'   => $token,
                'currency' => 'USD',
            ], ['api_key' => $this->apiKey]);
        } catch (InvalidRequestException $e) {
            throw new PaymentFailedException();
        }
    }

    public function getValidTestToken()
    {
        return Token::create([
            'card' => [
                'number'    => '4242424242424242',
                'exp_month' => 1,
                'exp_year'  => date('Y') + 1,
                'cvc'       => "123",
            ],
        ],[
            'api_key' => $this->apiKey,
        ])->id;
    }

    public function newChargesDuring($callback)
    {
        $latestCharge = $this->lastCharge();
        $callback($this);
        return $this->newChargesSince($latestCharge)
            ->pluck('amount');
    }

    private function lastCharge()
    {
        return Charge::all(
            ['limit' => 1],
            ['api_key' => $this->apiKey]
        )['data'][0];
    }

     private function newChargesSince($charge =null)
    {
        $newCharges = Charge::all(
            [
                'ending_before' => $charge ? $charge->id : null,
            ],
            ['api_key' => $this->apiKey]
        )['data'];

        return collect($newCharges);
    }
}