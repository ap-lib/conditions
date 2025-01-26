<?php declare(strict_types=1);

namespace AP\Conditions\Tests;

use AP\Conditions\All;
use AP\Conditions\LeastOne;
use AP\Conditions\Limit;
use PHPUnit\Framework\TestCase;

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

final class CafeOrderTest extends TestCase
{
    public function testSimpleAnd(): void
    {
        $salad = new All([
            new Limit(Protein::cases(), min: 0, max: 1),
            new Limit(Greens::cases(), min: 0, max: 2),
            new Limit(Vegetables::cases(), min: 1, max: 2),
            new LeastOne(Dressing::cases()),
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

        //////////////////////////////////////////////////////////////////////////
        // but double Dressing is available, because LeastOne no check dups by performance reason
        $this->assertTrue(
            $salad->check([
                Protein::Chicken,
                Greens::Spinach,
                Vegetables::Tomatoes,
                Toppings::Avocado,
                Dressing::Caesar, Dressing::Caesar, Dressing::Caesar, Dressing::Caesar,
                Extras::Rice,
            ])
        );

        //////////////////////////////////////////////////////////////////////////
        // probably with salad it is better to use all Conditions of the `Limit` type and no use `LeastOne` at all
        $fixed_salad = new All([
            new Limit(Protein::cases(), min: 0, max: 1),
            new Limit(Greens::cases(), min: 0, max: 2),
            new Limit(Vegetables::cases(), min: 1, max: 2),
            new Limit(Dressing::cases(), min: 1, max: 1),
            new Limit(Toppings::cases(), min: 0, max: 2),
            new Limit(Extras::cases(), min: 0, max: 2),
        ]);

        // the corrected salad won't allow duplicate dressing
        $this->assertFalse(
            $fixed_salad->check([
                Protein::Chicken,
                Greens::Spinach,
                Vegetables::Tomatoes,
                Toppings::Avocado,
                Dressing::Caesar, Dressing::Caesar, Dressing::Caesar, Dressing::Caesar,
                Extras::Rice,
            ])
        );

    }
}
