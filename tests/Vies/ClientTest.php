<?php

namespace Ddeboer\Vatin\Test\Vies;

use Ddeboer\Vatin\Vies\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testCheckVat(): void
    {
        $client = new Client();
        $response = $client->checkVat('NL', '123456789B01');

        $this->assertFalse($response->valid);
        $this->assertEquals('NL', $response->countryCode);
        $this->assertEquals('123456789B01', $response->vatNumber);
        $this->assertInstanceOf(\DateTimeImmutable::class, $response->date);
        $this->assertEquals('---', $response->name);
        $this->assertEquals('---', $response->address);
    }
}
