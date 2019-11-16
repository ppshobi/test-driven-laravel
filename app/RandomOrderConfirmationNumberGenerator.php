<?php
namespace App;

class RandomOrderConfirmationNumberGenerator implements OrderConfirmationNumberGenerator
{
    public function generate()
    {
        $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ23456789';

        return substr(str_shuffle(str_repeat($pool, 24)), 0, 24);
    }
}