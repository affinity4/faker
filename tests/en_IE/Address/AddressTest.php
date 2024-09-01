<?php declare(strict_types=1);

namespace Affinity4\Faker\Tests\en_IE\Address;

use Affinity4\Faker\en_IE\Address\Address;
use Faker\Factory;
use Faker\Generator;
use Faker\Provider\Base;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

class AddressTest extends TestCase
{
    private Generator $faker;
    private Address $address;

    public function setUp(): void
    {
        $this->faker = Factory::create();
        $this->address = new Address($this->faker);

        $this->faker->addProvider($this->address);
    }

    public function testAddressExtendsFakerBase(): void
    {
        self::assertTrue($this->address instanceof Base);
    }

    #[Depends('testAddressExtendsFakerBase')]
    public function testCountryIsIreland(): void
    {
        self::assertEquals('Ireland', $this->address->country());
    }

    #[Depends('testAddressExtendsFakerBase')]
    public function testEircodeWithSpace(): void
    {
        self::assertMatchesRegularExpression($this->getEircodeRegex(), $this->address->eircode());
    }

    #[Depends('testAddressExtendsFakerBase')]
    public function testEircodeWithoutSpace(): void
    {
        self::assertMatchesRegularExpression($this->getEircodeRegex(false), $this->address->eircode(!Address::WITH_SPACE));
    }

    #[Depends('testAddressExtendsFakerBase')]
    public function testEircodeDoesNotUseExistingRoutingKeys(): void
    {
        [$routing_key,] = explode(' ', $this->address->eircode(Address::WITH_SPACE, !Address::ALLOW_EXISTING_ROUTING_KEYS));

        self::assertTrue(!in_array($routing_key, Address::EXISTING_ROUTING_KEYS));
    }

    #[Depends('testAddressExtendsFakerBase')]
    public function testProvince(): void
    {
        self::assertTrue(in_array($this->address->province(), Address::VALID_PROVINCES));
    }

    #[Depends('testAddressExtendsFakerBase')]
    public function testCounty(): void
    {
        self::assertTrue(in_array($this->address->county(), Address::VALID_COUNTIES));
    }

    #[Depends('testAddressExtendsFakerBase')]
    public function testCountyWithPrefix(): void
    {
        self::assertTrue(str_starts_with($this->address->county(Address::WITH_PREFIX), 'Co.'));
    }

    #[Depends('testAddressExtendsFakerBase')]
    public function testTown(): void
    {
        $town = $this->address->town();
        $towns = require dirname(__DIR__, 3) . '/src/en_IE/data/towns/' . $this->address->getCurrentTownLookupIndex() . '.php';

        self::assertTrue(in_array($town, $towns));
    }

    #[Depends('testAddressExtendsFakerBase')]
    public function testStreet(): void
    {
        [$streetPrefix, $streetSuffix] = explode(' ', $this->address->street());

        self::assertTrue(in_array($streetPrefix, Address::STREET_PREFIXES), "Failed asserting that $streetPrefix is in Address::STREET_PREFIXES");
        self::assertTrue(in_array($streetSuffix, Address::STREET_SUFFIXES), "Failed asserting that $streetSuffix is in Address::STREET_SUFFIXES");
    }

    #[Depends('testAddressExtendsFakerBase')]
    public function testBuildingNumber(): void
    {
        [$regex] = $this->getBuildingNumberRegexParts();
        self::assertMatchesRegularExpression('/^' . $regex . '$/', $this->address->buildingNumber());
    }

    #[Depends('testCountryIsIreland')]
    #[Depends('testEircodeWithSpace')]
    #[Depends('testTown')]
    #[Depends('testStreet')]
    #[Depends('testBuildingNumber')]
    public function testAddress(): void
    {
        $address = $this->address->address();
        $towns = require dirname(__DIR__, 3) . '/src/en_IE/data/towns/' . $this->address->getCurrentTownLookupIndex() . '.php';

        $buildingNumberRegex = '(' . $this->getBuildingNumberRegexParts()[0] . ')';
        $streetRegex = '(' . implode('|', Address::STREET_PREFIXES) . ') (' . implode('|', Address::STREET_SUFFIXES) . ')';
        $townRegex = '(' . implode('|', $towns) . ')';
        $countyRegex = '(' . implode('|', Address::VALID_COUNTIES) . ')';
        $eircodeRegexParts = $this->getEircodeRegexParts(); 
        $eircodeRegex = '(' . implode(' ', $eircodeRegexParts) . ')';
        $regex = '/^'. $buildingNumberRegex  . ' ' . $streetRegex . ', ' . $townRegex . ', ' . $countyRegex . ', Ireland, ' . $eircodeRegex . '$/';

        self::assertMatchesRegularExpression($regex, $address);
    }

    #[Depends('testEircodeWithSpace')]
    #[Depends('testBuildingNumber')]
    #[Depends('testCounty')]
    #[Depends('testCountryIsIreland')]
    public function testAddProviderOverridesMethods(): void
    {
        // NOTE: We only need to check a few to be sure the provider is correctly added:
        // a. New methods exist, such as eircode
        // b. existing methods have been overwritten, such as buildingNumber, County and Country

        // Test eircode exists
        $eircode = $this->faker->eircode();
        self::assertTrue(is_string($eircode));

        // Test building number
        [$regex] = $this->getBuildingNumberRegexParts();
        self::assertMatchesRegularExpression('/^' . $regex . '$/', $this->faker->buildingNumber());

        // Test county is Irish
        self::assertTrue(in_array($this->address->county(), Address::VALID_COUNTIES));

        // Test country is Ireland
        self::assertEquals('Ireland', $this->faker->country());
    }

    private function getBuildingNumberRegexParts(): array
    {
        return ['\d{1,3}[A-Z]?'];
    }

    private function getEircodeRegexParts(): array
    {
        $validLetters = '[ABCDEFGHIJKLMNPQRSTUVWXYZ]'; // There is no 'O' anywhere in an Eircode so as to not get mixed up with zero
        $routingKeyRegex = $validLetters . '\d{2}';
        $uniqueIdentifierRegexes = [
            $validLetters . '{1}\d{3}', // A123
            $validLetters . '{2}\d{2}', // AB23
            $validLetters . '{3}\d{1}', // ABC3
            $validLetters . '{2}\d{1}' . $validLetters . '{1}', // AB2C
            $validLetters . '{1}\d{2}' . $validLetters . '{1}', // A23C
            $validLetters . '{1}\d{1}' . $validLetters . '{2}', // A2BC
            $validLetters . '{1}\d{1}' . $validLetters . '{1}\d{1}', // A2B3
            '\d{1}' . $validLetters . '{1}\d{2}', // 2A34
            '\d{1}' . $validLetters . '{2}\d{1}', // 2AB4
            '\d{1}' . $validLetters . '{3}', // 2ABC
            '\d{1}' . $validLetters . '{1}\d{1}' . $validLetters . '{1}', // 2A3C
            '\d{2}' . $validLetters . '{1}\d{1}', // 23B4
            '\d{2}' . $validLetters . '{2}', // 23AB
            '\d{3}' . $validLetters . '{1}', // 234B

        ];
        
        $uniqueIdentifierRegex = '((' . implode(')|(', $uniqueIdentifierRegexes) . '))';

        return [$routingKeyRegex, $uniqueIdentifierRegex];
    }

    private function getEircodeRegex($withSpace = true): string
    {
        [$routingKeyRegex, $uniqueIdentifierRegex] = $this->getEircodeRegexParts();
        return ($withSpace) 
            ? '/^' . $routingKeyRegex . ' ' . $uniqueIdentifierRegex . '$/'
            : '/^' . $routingKeyRegex . $uniqueIdentifierRegex . '$/';
    }
}
