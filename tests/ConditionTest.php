<?php declare(strict_types=1);

namespace AP\Conditions\Tests;

use AP\Conditions\All;
use AP\Conditions\LeastOne;
use AP\Conditions\Limit;
use PHPUnit\Framework\TestCase;

final class ConditionTest extends TestCase
{
    public function testLeastOne(): void
    {
        $options = new LeastOne(['orange', 'apple', 'banana']);

        $this->assertTrue($options->check(['orange', 'apple']));
        $this->assertTrue($options->check(['PINEAPPLE', 'orange', 'apple']));
        $this->assertTrue($options->check(['orange', 'apple', 'PINEAPPLE']));
        $this->assertTrue($options->check(['orange', 'apple', 'banana']));
        $this->assertTrue($options->check(['PINEAPPLE', 'banana']));
        $this->assertTrue($options->check(['banana']));
        $this->assertTrue($options->check(['banana', 'PINEAPPLE']));
        $this->assertTrue($options->check(['PINEAPPLE', 'banana', 'PINEAPPLE']));

        $this->assertFalse($options->check(['PINEAPPLE']));
    }

    public function testAll(): void
    {
        $conditions = new All(['orange', 'apple', 'banana']);

        $this->assertTrue($conditions->check(['orange', 'apple', 'banana']));
        $this->assertTrue($conditions->check(['orange', 'apple', 'banana', 'PINEAPPLE']));
        $this->assertTrue($conditions->check(['PINEAPPLE', 'orange', 'apple', 'banana']));
        $this->assertTrue($conditions->check(['PINEAPPLE', 'orange', 'PINEAPPLE', 'apple', 'banana']));
        $this->assertTrue($conditions->check(['orange', 'PINEAPPLE', 'apple', 'banana']));

        $this->assertFalse($conditions->check(['banana', 'PINEAPPLE']));
        $this->assertFalse($conditions->check(['PINEAPPLE']));
    }

    public function testLimit(): void
    {
        $condition_1_2 = new Limit(['orange', 'apple', 'banana'], 1, 2);

        $this->assertTrue($condition_1_2->check(['orange', 'A']));
        $this->assertTrue($condition_1_2->check(['orange', 'A', 'B']));
        $this->assertTrue($condition_1_2->check(['A', 'B', 'orange']));
        $this->assertTrue($condition_1_2->check(['orange', 'apple']));

        $this->assertFalse($condition_1_2->check(['A']));
        $this->assertFalse($condition_1_2->check(['A', 'B']));
        $this->assertFalse($condition_1_2->check(['orange', 'apple', 'banana']));
    }
}
