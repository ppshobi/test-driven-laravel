<?php
namespace Tests\Unit;

use App\Order;
use App\Ticket;
use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use App\HashIdsTicketCodeGenerator;
use App\RandomOrderConfirmationNumberGenerator;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class HashIdsTicketCodeGeneratorTest extends TestCase
{
    /**
     * @test
     **/
    function ticket_codes_are_atleast_6_characters_long()
    {
        $ticketCodeGenerator = new HashIdsTicketCodeGenerator('test_salt_1');
        $code = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));

        $this->assertTrue(strlen($code) >= 6);
    }

    /**
     * @test
     **/
    function ticket_codes_can_only_contain_uppercase_letters()
    {
        $ticketCodeGenerator = new HashIdsTicketCodeGenerator('test_salt_1');
        $code = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));

        $this->assertRegExp('/^[A-Z]+$/', $code);
    }

    /**
     * @test
     **/
    function ticket_codes_for_same_ticket_id_are_same()
    {
        $ticketCodeGenerator = new HashIdsTicketCodeGenerator('test_salt_1');

        $code1 = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));
        $code2 = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));

        $this->assertEquals($code1, $code2);
    }

    /**
     * @test
     **/
    function ticket_codes_for_different_ticket_id_are_different()
    {
        $ticketCodeGenerator = new HashIdsTicketCodeGenerator('test_salt_1');

        $code1 = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));
        $code2 = $ticketCodeGenerator->generateFor(new Ticket(['id' => 2]));

        $this->assertNotEquals($code1, $code2);
    }

    /**
     * @test
     **/
    function ticket_codes_generated_with_different_salts_are_different()
    {
        $ticketCodeGenerator1 = new HashIdsTicketCodeGenerator('test_salt_1');
        $ticketCodeGenerator2 = new HashIdsTicketCodeGenerator('test_salt_2');

        $code1 = $ticketCodeGenerator1->generateFor(new Ticket(['id' => 1]));
        $code2 = $ticketCodeGenerator2->generateFor(new Ticket(['id' => 1]));

        $this->assertNotEquals($code1, $code2);
    }
}