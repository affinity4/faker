<?php declare(strict_types=1);

namespace Affinity4\Faker\en_IE\Address;

use Faker\Provider\Base;

class Address extends Base
{
    public const VALID_PROVINCES = [
        'Connacht', 'Leinster', 'Munster', 'Ulster'
    ];
    
    public const VALID_COUNTIES = [
        // Connacht
        'Galway', 'Leitrim', 'Mayo', 'Sligo', 'Roscommon',
        
        // Leinster
        'Carlow', 'Dublin', 'Kildare', 'Kilkenny', 'Laois',
        'Longford', 'Louth', 'Meath', 'Offaly',
        'Westmeath', 'Wexford', 'Wicklow',
        
        // Munster
        'Clare', 'Cork', 'Kerry', 'Limerick',
        'Tipperary', 'Waterford',
        
        // Ulster
        'Cavan', 'Donegal', 'Monaghan',
    ];

    public const EXISTING_ROUTING_KEYS = [
        'A41', 'A42', 'A45', 'A63', 'A67', 'A75', 'A81', 'A82', 'A83', 
        'A84', 'A85', 'A86', 'A91', 'A92', 'A94', 'A96', 'A98', 'C15', 
        'D01', 'D02', 'D03', 'D04', 'D05', 'D06', 'D07', 'D08', 'D09', 
        'D10', 'D11', 'D12', 'D13', 'D14', 'D15', 'D16', 'D17', 'D18',
        'D20', 'D22', 'D24', 'D6W', 'E21', 'E25', 'E32', 'E34', 'E41',
        'E45', 'E53', 'E91', 'F12', 'F23', 'F26', 'F28', 'F31', 'F35',
        'F42', 'F45', 'F52', 'F56', 'F91', 'F92', 'F93', 'F94', 'H12',
        'H14', 'H16', 'H18', 'H23', 'H53', 'H54', 'H62', 'H65', 'H71',
        'H91', 'K32', 'K34', 'K36', 'K45', 'K56', 'K67', 'K78', 'N37', 
        'N39', 'N41', 'N91', 'P12', 'P14', 'P17', 'P24', 'P25', 'P31', 
        'P32', 'P36', 'P43', 'P47', 'P51', 'P56', 'P61', 'P67', 'P72', 
        'P75', 'P81', 'P85', 'R14', 'R21', 'R32', 'R35', 'R42', 'R45',
        'R56', 'R51', 'R93', 'R95', 'T12', 'T23', 'T34', 'T45', 'T56',
        'V14', 'V15', 'V23', 'V31', 'V35', 'V42', 'V92', 'V93', 'V94', 
        'V95', 'W12', 'W23', 'W34', 'W91', 'X35', 'X42', 'X91', 'Y14',
        'Y21', 'Y25', 'Y34', 'Y35',
    ];

    public const STREET_PREFIXES = [
        'Abbey', 
        'Ailesbury',
        'Amiens',
        'Anglesea',
        'Baggot',
        'Bayside',
        'Bridge',
        'Castle',
        'Chapel',
        'Carrick',
        'Claremorris',
        'Claremont',
        'Clyde',
        'Crampton',
        'Cumberland',
        'Dame',
        'Dawson',
        'Eccles',
        'Elm',
        'Eyre',
        'FitzPatric',
        'FitzWilliam',
        'George',
        'Grafton',
        'Greenfield',
        'Golden',
        'Harcourt',
        'Henrietta',
        'Leeson',
        'Market',
        'Memorial',
        'Merchat\'s',
        'Merrion',
        'Newbridge',
        'Newcastle',
        'Park',
        'Parliament',
        'Parnell',
        'Pearse',
        'Sandymount',
        'Strand',
        'Sycamore',
        'Temple',
    ];

    public const STREET_SUFFIXES = [
        'Drive',
        'Fields',
        'Gardens',
        'Heights',
        'Lane',
        'Park',
        'Place',
        'Road',
        'Street',
        'Square',
        'Terrace',
        'Quay',
    ];
    
    public const WITH_SPACE = true;
    public const WITH_PREFIX = true;
    public const ALLOW_EXISTING_ROUTING_KEYS = true;

    protected const COUNTRY = 'Ireland';
    protected const VALID_EIRCODE_LETTERS = 'ABCDEFGHIJKLMNPQRSTUVWXYZ'; // No 'O' letter
    protected const VALID_EIRCODE_DIGITS = '0123456789';

    private int $currentTownLookupIndex = 1;

    public function country(): string
    {
        return self::COUNTRY;
    }

    /**
     * @param bool $withSpace                   Whether or not the eircode should have a space between the routing key and the unique identifier e.g A23 B2C3. Default: true
     * @param bool $allowExistingRoutingKeys    Whether or not the routing key should include existing (real world) routing keys or not. Set this to false if you want to ensure your eircode is fake. Default: true
     */
    public function eircode($with_space = self::WITH_SPACE, $allow_existing_routing_keys = self::ALLOW_EXISTING_ROUTING_KEYS): string
    {
        $pattern = '%s %s';
        if ($with_space !== self::WITH_SPACE) {
            $pattern = '%s%s';
        }

        return sprintf($pattern, $this->routingKey($allow_existing_routing_keys), $this->uniqueIdentifier());
    }

    /**
     * @return string
     */
    public function province(): string
    {
        return static::randomElement(self::VALID_PROVINCES);
    }

    /**
     * @param bool $withPrefix
     * 
     * @return string
     */
    public function county($with_prefix = !self::WITH_PREFIX): string
    {
        if ($with_prefix) {
            return 'Co. ' . static::randomElement(self::VALID_COUNTIES); 
        }

        return static::randomElement(self::VALID_COUNTIES);
    }

    /**
     * To preserve memory, we do not load all towns as one array, as there are far too many. Instead, we first select a integer at random, 
     * and from there choose a town in that file e.g. data/towns/2.php, which has all towns from C to J.
     * 
     * @return string
     */
    public function town(): string
    {
        $this->currentTownLookupIndex = static::numberBetween(1, 4);

        $towns = require dirname(__DIR__) . '/data/towns/' . $this->currentTownLookupIndex . '.php';

        return static::randomElement($towns);
    }

    /**
     * @return int
     */
    public function getCurrentTownLookupIndex(): int
    {
        return $this->currentTownLookupIndex;
    }

    /**
     * @return string
     */
    public function street(): string
    {
        return sprintf('%s %s', static::randomElement(self::STREET_PREFIXES), static::randomElement(self::STREET_SUFFIXES));
    }

    /**
     * @return string
     */
    public function address(): string
    {
        return sprintf(
            '%s %s, %s, %s, %s, %s',
            $this->buildingNumber(),
            $this->street(),
            $this->town(),
            $this->county(),
            $this->country(),
            $this->eircode(),
        );
    }

    /**
     * @return string
     */
    public function buildingNumber(): string
    {
        $buildingNumber = static::numberBetween(1, 399);
        $hasLetterSuffix = (bool) static::randomDigit(0, 1);
        if ($hasLetterSuffix) {
            return  $buildingNumber .  static::randomElement(['A', 'B', 'C']);
        }

        return (string) $buildingNumber;
    }

    /**
     * @param bool $allowExistingRoutingKeys
     * 
     * @return string
     */
    protected function routingKey(bool $allow_existing_routing_keys): string
    {
        $validLetters = str_split(self::VALID_EIRCODE_LETTERS);

        $routingKey = static::randomELement($validLetters) . static::numberBetween(10, 99);
        if (!$allow_existing_routing_keys) {
            do {
                $routingKey = static::randomELement($validLetters) . static::numberBetween(10, 99);
            } while (in_array($routingKey, self::EXISTING_ROUTING_KEYS));
        }

        return $routingKey;
    }

    /**
     * @return string
     */
    protected function uniqueIdentifier(): string
    {
        $allChars = self::VALID_EIRCODE_LETTERS . self::VALID_EIRCODE_DIGITS;
        $string = '';

        // Ensure we always have at least 1 letter and 1 digit...
        $string .= self::VALID_EIRCODE_LETTERS[rand(0, strlen(self::VALID_EIRCODE_LETTERS) - 1)];
        $string .= self::VALID_EIRCODE_DIGITS[rand(0, strlen(self::VALID_EIRCODE_DIGITS) - 1)];

        // Fill the remaining 2 characters
        for ($i = 0; $i < 2; $i++) {
            $string .= $allChars[rand(0, strlen($allChars) - 1)];
        }

        return str_shuffle($string);
    }
}
