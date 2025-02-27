<?php

namespace Ddeboer\Vatin\Test;

use Ddeboer\Vatin\Exception\ServiceUnreachableException;
use Ddeboer\Vatin\Exception\ViesException;
use Ddeboer\Vatin\Validator;
use Ddeboer\Vatin\Vies\Client;
use Ddeboer\Vatin\Vies\ClientInterface;
use Ddeboer\Vatin\Vies\Response\CheckVatResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    #[DataProvider('getValidVatins')]
    public function testValid(string $value): void
    {
        $validator = new Validator();
        $this->assertTrue($validator->isValid($value));
    }

    #[DataProvider('getInvalidVatins')]
    public function testInvalid(?string $value): void
    {
        $validator = new Validator();
        $this->assertFalse($validator->isValid($value));
    }

    public function testValidWithVies(): void
    {
        $client = $this->getViesClientMock();
        $client
            ->expects($this->once())
            ->method('checkVat')
            ->with('NL', '002065538B01')
            ->willReturn(new CheckVatResponse(
                'NL', '002065538B01', new \DateTimeImmutable(), true, '---', '---',
            ));

        $validator = new Validator($client);
        $this->assertTrue($validator->isValid('NL002065538B01', true));
    }

    public function testInvalidWithVies(): void
    {
        $client = $this->getViesClientMock();
        $client
            ->expects($this->once())
            ->method('checkVat')
            ->with('NL', '123456789B01')
            ->willReturn(new CheckVatResponse(
                'NL', '123456789B01', new \DateTimeImmutable(), false, '---', '---',
            ));

        $validator = new Validator($client);
        $this->assertFalse($validator->isValid('NL123456789B01', true));
    }

    public function testWrongConnectionThrowsException(): void
    {
        $this->expectException(ServiceUnreachableException::class);

        $validator = new Validator(new Client('meh'));
        $validator->isValid('NL002065538B01', true);
    }

    /**
     * @return list<list{0: non-empty-string}>
     */
    public static function getValidVatins(): array
    {
        return [
            // Examples from Wikipedia (https://en.wikipedia.org/wiki/VAT_identification_number)
            ['ATU99999999'],           // Austria
            ['BE0999999999'],          // Belgium
            ['BE1999999999'],          // Belgium
            ['HR12345678901'],         // Croatia
            ['CY99999999L'],           // Cyprus
            ['DK99999999'],            // Denmark
            ['FI99999999'],            // Finland
            ['FRXX999999999'],         // France
            ['DE999999999'],           // Germany
            ['HU12345678'],            // Hungary
            ['IE1234567T'],            // Ireland
            ['IE1234567TW'],           // Ireland
            ['IE1234567FA'],           // Ireland (since January 2013)
            ['NL999999999B99'],        // The Netherlands
            ['NO999999999'],           // Norway
            ['NO999999999MVA'],        // Norway (including MVA)
            ['ES99999999R'],           // Spain
            ['SE999999999901'],        // Sweden
            ['CHE-123.456.788 TVA'],   // Switzerland
            ['GB999999973'],           // United Kingdom (standard)
            ['GBGD001'],               // United Kingdom (government departments)
            ['GBHA599'],               // United Kingdom (health authorities)

            // Examples from the EU (http://ec.europa.eu/taxation_customs/vies/faqvies.do#item_11)
            ['ATU99999999'],           // AT-Austria
            ['BE0999999999'],          // BE-Belgium
            ['BG999999999'],           // BG-Bulgaria
            ['BG9999999999'],          // BG-Bulgaria
            ['CY99999999L'],           // CY-Cyprus
            ['CZ99999999'],            // CZ-Czech Republic
            ['CZ999999999'],           // CZ-Czech Republic
            ['CZ9999999999'],          // CZ-Czech Republic
            ['DE999999999'],           // DE-Germany
            ['DK99999999'],            // DK-Denmark
            ['EE999999999'],           // EE-Estonia
            ['EL999999999'],           // EL-Greece
            ['ESX9999999X'],           // ES-Spain
            ['FI99999999'],            // FI-Finland
            ['FRXX999999999'],         // FR-France
            ['GB999999999'],           // GB-United Kingdom
            ['GB999999999999'],        // GB-United Kingdom
            ['GBGD999'],               // GB-United Kingdom
            ['GBHA999'],               // GB-United Kingdom
            ['HR99999999999'],         // HR-Croatia
            ['HU99999999'],            // HU-Hungary
            ['IE9S99999L'],            // IE-Ireland
            ['IE9999999WI'],           // IE-Ireland
            ['IT99999999999'],         // IT-Italy
            ['LT999999999'],           // LT-Lithuania
            ['LT999999999999'],        // LT-Lithuania
            ['LU99999999'],            // LU-Luxembourg
            ['LV99999999999'],         // LV-Latvia
            ['MT99999999'],            // MT-Malta
            ['NL999999999B99'],        // NL-The Netherlands
            ['PL9999999999'],          // PL-Poland
            ['PT999999999'],           // PT-Portugal
            ['RO999999999'],           // RO-Romania
            ['SE999999999999'],        // SE-Sweden
            ['SI99999999'],            // SI-Slovenia
            ['SK9999999999'],          // SK-Slovakia

            // Real world examples
            ['GB226148083'],           // Fuller's Brewery, United Kingdom
            ['NL002230884B01'],        // Albert Heijn BV., The Netherlands
            ['ESG82086810'],           // Fundación Telefónica, Spain
            ['IE9514041I'],            // Lego Systems A/S, Denmark with Irish VAT ID
            ['IE9990705T'],            // Amazon EU Sarl, Luxembourg with Irish VAT ID
            ['DK61056416'],            // Carlsberg A/S, Denmark
            ['BE0648836958'],          // Delhaize Logistics, Belgium
            ['CZ00514152'],            // Budějovický Budvar, Budweiser, Czech Republic

            // Various examples
            ['FR9X999999999'],
            ['NL123456789B01'],
            ['IE9574245O'],
            ['CHE123456788TVA'],
        ];
    }

    /**
     * @return list<list{0: string|null}>
     */
    public static function getInvalidVatins(): array
    {
        return [
            [null],
            [''],
            ['123456789'],
            ['XX123'],
            ['GB999999973dsflksdjflsk'],
            ['BE2999999999'],          // Belgium - "the first digit following the prefix is always zero ("0") or ("1")"
            ['CHE12345678 MWST'],
        ];
    }

    private function getViesClientMock(): MockObject&ClientInterface
    {
        return $this->getMockBuilder(ClientInterface::class)
            ->getMock();
    }
}
