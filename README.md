# Faker Irish (en_IE) Providers

Faker Providers for Ireland specific data (Streets, Towns, Counties, Eircodes, OSI Grid References, Landline Phone Numbers, Mobile Phone NUmbers). NOTE: Does not include Northern Ireland

## en_IE/Address

### Installation

```base
composer require --dev affinity4/faker
```

### Configuration

Configure your instance of Faker to use the provider

```php
$faker = \Faker\Factory::create();
$faker->addProvider(new \Affinity4\Faker\en_IE\Address\Address($faker));

$faker->address(); // 123C Pearse Heights, Arklow, Galway, Ireland, V50 Z9U1
```

### Usage

#### Address

```php
$faker->address(); // 123C Pearse Heights, Arklow, Galway, Ireland, V50 Z9U1
```

#### Building Number

```php
$faker->buildingNumber(); // 123 or 123C
```

#### Street

```php
$faker->street(); // Pearse Heights
```

#### Town

```php
$faker->town(); // Arklow
```

#### County

```php
$faker->county(); // Galway

// or...
$faker->county(with_prefix: true); // Co. Galway
```

#### Province

```php
$faker->province(); // Connacht
```

#### Country

```php
$faker->country(); // Ireland
```

#### Eircode

```php
$faker->eircode(); // V50 Z9U1

// or...
$faker->eircode(with_spaces: false); // V50Z9U1
```

You can also tell it not to use valid Eircodes by ensuring that the Routing Key (the first 3 characters) are never in real eircode routing keys. This is useful if you want to ensure your eircodes are definitely always fake.

```php
$faker->eircode(allow_existing_routing_keys: false); // Z99 Z9U1
```

## License

This project is licensed under the MIT License - see the LICENSE file for details

## Contributing

Feel free to submit issues or pull requests if you have any suggestions or improvements.
