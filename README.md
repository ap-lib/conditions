# AP\Conditions

[![MIT License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

A powerful and flexible PHP library for managing conditions.

## Installation

```bash
composer require ap-lib/conditions
```

## Features
- Define complex logical conditions.
- Validate data against custom conditions.
- Normalize and denormalize conditions for serialization.

## Requirements
- PHP 8.3 or higher

## Getting Started
### Consents example
you have a partners list:
```
| ID  | Name                  |
|-----|-----------------------|
| 1   | Apple                 |
| 2   | Banana                |
| 3   | Potato                |
| 4   | Pineapple             |
| 5   | Orange                |
| ... | ...                   |
| 11  | Apple LLC             |
| 12  | Banana Corporation    |
| 15  | Orange Co             |
| ... | ...                   |
| 25  | Orange.com            |
| ... | ...                   |
```

You have a function to get all approved companies by phone number:
```php
/**
 * @return int[]
 */
function getConsents(int $phone): array;
```
You now intend to use only clients who have provided consent for the following conditions:

(Apple **OR** Apple LLC) **AND** (Banana **OR** Banana Corporation) **AND** (Potato) **AND** (Pineapple) **AND** (Orange **OR** Orange Co **OR** Orange.com)

Additionally, you want to have an API, enabling your workers to view and modify them as needed.


we can describe this logic on json format:
```json
{
  "type": "all",
  "data": [
    {
      "type": "least_one",
      "data": [1, 11]
    },
    {
      "type": "least_one",
      "data": [2, 12]
    },
    3,
    4,
    {
      "type": "least_one",
      "data": [5, 15, 25]
    }
  ]
}
```

to check client by json  conditions we can use it:

```php
function loadJsonConditions(): string
{
    return '{"type":"all","data":[{"type":"least_one","data":[1,11]},{"type":"least_one","data":[2,12]},3,4,{"type":"least_one","data":[5,15,25]}]}';
}

function getConsents(int $phone): array
{
    return match ($phone) {
        1234567890 => [1, 12, 3, 4, 25, 8, 80],
        9876543210 => [11, 2, 3, 4, 5],
        9998887777 => [1, 2, 3, 11],
        default => [],
    };
}

$json_conditions = loadJsonConditions();
$normalizer      = new Normalizer();
$conditions      = $normalizer->denormalize(json_decode($json_conditions, true));

$conditions->check(getConsents(1234567890)); // true
$conditions->check(getConsents(9876543210)); // true
$conditions->check(getConsents(9998887777)); // false
$conditions->check(getConsents(1112223333)); // false
```


### Building a Salad example
You can define a condition to validate salad ingredient combinations:
```php
use AP\Conditions\All;
use AP\Conditions\Limit;
use AP\Conditions\LeastOne;


enum Protein
{
    case Chicken;
    case Beef;
    case Salmon;
    case Tofu;
    case Shrimp;
}

enum Greens
{
    case Lettuce;
    case Spinach;
    case Kale;
    case Arugula;
    case Cabbage;
    case Romaine;
}

enum Vegetables
{
    case Tomatoes;
    case Cucumbers;
    case Carrots;
    case BellPeppers;
    case Onions;
    case Radishes;
}

enum Toppings
{
    case Croutons;
    case Seeds;
    case Nuts;
    case Cheese;
    case Avocado;
    case Olives;
    case Garlic;
}

enum Dressing
{
    case Ranch;
    case Vinaigrette;
    case Caesar;
    case Balsamic;
    case HoneyMustard;
    case YogurtDressing;
    case OliveOil;
    case SourCream;
}

enum Extras
{
    case Quinoa;
    case Pasta;
    case Rice;
    case Potatoes;
    case Breadsticks;
}

$salad = new All([
    new Limit(Protein::cases(), min: 0, max: 1),
    new Limit(Greens::cases(), min: 0, max: 2),
    new Limit(Vegetables::cases(), min: 1, max: 2),
    new Limit(Dressing::cases(), min: 1, max: 1),
    new Limit(Toppings::cases(), min: 0, max: 2),
    new Limit(Extras::cases(), min: 0, max: 2),
]);

//////////////////////////////////////////////////////////////////////////
// good choice
$this->assertTrue(
    $salad->check([
        Protein::Chicken,
        Greens::Spinach,
        Vegetables::Tomatoes,
        Toppings::Avocado,
        Dressing::OliveOil,
        Extras::Rice,
    ])
);

//////////////////////////////////////////////////////////////////////////
// I'm not hungry, russian summer greens salad, please
$this->assertTrue(
    $salad->check([
        Vegetables::Tomatoes,
        Vegetables::Cucumbers,
        Toppings::Garlic,
        Dressing::SourCream,
    ])
);

//////////////////////////////////////////////////////////////////////////
// double Vegetables
$this->assertTrue(
    $salad->check([
        Protein::Chicken,
        Greens::Spinach,
        Vegetables::Tomatoes, Vegetables::Cucumbers,
        Toppings::Avocado,
        Dressing::OliveOil,
        Extras::Rice,
    ])
);

//////////////////////////////////////////////////////////////////////////
// vegetarian option with no protein
$this->assertTrue(
    $salad->check([
        Greens::Spinach,
        Vegetables::Tomatoes, Vegetables::Cucumbers,
        Toppings::Avocado,
        Dressing::Caesar,
        Extras::Rice,
    ])
);

//////////////////////////////////////////////////////////////////////////
// oh, no. too much Protein
$this->assertFalse(
    $salad->check([
        Protein::Chicken, Protein::Salmon,
        Greens::Spinach,
        Vegetables::Tomatoes,
        Toppings::Avocado,
        Dressing::Caesar,
        Extras::Rice,
    ])
);

//////////////////////////////////////////////////////////////////////////
// unfortunately, double protein no available either
$this->assertFalse(
    $salad->check([
        Protein::Chicken, Protein::Chicken,
        Greens::Spinach,
        Vegetables::Tomatoes,
        Toppings::Avocado,
        Dressing::Caesar,
        Extras::Rice,
    ])
);

//////////////////////////////////////////////////////////////////////////
// btw, if you need, you can ignore dups
$options_with_ignore_dups = new All([
    new Limit(Protein::cases(), min: 0, max: 1, ignore_duplicates: true),
    new Limit(Greens::cases(), min: 0, max: 2, ignore_duplicates: true),
    new Limit(Vegetables::cases(), min: 1, max: 2, ignore_duplicates: true),
    new LeastOne(Dressing::cases()),
    new Limit(Toppings::cases(), min: 0, max: 2, ignore_duplicates: true),
    new Limit(Extras::cases(), min: 0, max: 2, ignore_duplicates: true),
]);


// now it works
$this->assertTrue(
    $options_with_ignore_dups->check([
        Protein::Chicken, Protein::Chicken, Protein::Chicken,
        Greens::Spinach, Greens::Spinach, Greens::Spinach,
        Vegetables::Tomatoes, Vegetables::Tomatoes, Vegetables::Tomatoes,
        Toppings::Avocado, Toppings::Avocado, Toppings::Avocado,
        Dressing::Caesar, Dressing::Caesar, Dressing::Caesar,
        Extras::Rice, Extras::Rice, Extras::Rice,
    ])
);
```