<?php

namespace App\Providers;

use App\TicketCodeGenerator;
use App\Billing\PaymentGateway;
use App\HashIdsTicketCodeGenerator;
use App\Billing\StripePaymentGateway;
use Illuminate\Support\ServiceProvider;
use App\OrderConfirmationNumberGenerator;
use App\RandomOrderConfirmationNumberGenerator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(StripePaymentGateway::class, function (){
            return new StripePaymentGateway(config('services.stripe.secret'));
        });

        $this->app->bind(OrderConfirmationNumberGenerator::class, function (){
            return new RandomOrderConfirmationNumberGenerator();
        });
        $this->app->bind(HashIdsTicketCodeGenerator::class, function(){
            return new HashIdsTicketCodeGenerator(config('app.ticket_code_salt'));
        });
        $this->app->bind(TicketCodeGenerator::class,HashIdsTicketCodeGenerator::class);

        $this->app->bind(PaymentGateway::class, StripePaymentGateway::class);
    }
}
