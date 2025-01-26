<?php declare(strict_types=1);

namespace AP\Conditions\Tests;

use AP\Conditions\All;
use AP\Conditions\LeastOne;
use AP\Conditions\Normalizer;
use PHPUnit\Framework\TestCase;

final class ConsentsTest extends TestCase
{
    public function testMainProcess(): void
    {
        $required_consents = new All([
            new LeastOne([1, 11]),
            new LeastOne([2, 12]),
            3,
            4,
            new LeastOne([5, 15, 25]),
        ]);


        $have_consents = [1, 12, 3, 4, 25, 8, 80];
        $can_use       = $required_consents->check($have_consents);

        $used_options = $required_consents->get_all_options_recursive();

        $this->assertTrue($can_use); // test use case
        $this->assertEqualsCanonicalizing([1, 11, 2, 12, 3, 4, 5, 15, 25], $used_options); // test list used on condition elements
    }

    public function testNormalizer(): void
    {
        $normalaizer = new Normalizer();

        $original_required_consents = new All([
            new LeastOne([1, 11]),
            new LeastOne([2, 12]),
            3,
            4,
            new LeastOne([5, 15, 25]),
        ]);

        $normalized_required_consents = $normalaizer->normalize($original_required_consents);

        $expected_json = '{"type":"all","data":[{"type":"least_one","data":[1,11]},{"type":"least_one","data":[2,12]},3,4,{"type":"least_one","data":[5,15,25]}]}';

        $this->assertEquals($expected_json, json_encode($normalized_required_consents));

        $denormalized_required_consents = $normalaizer->denormalize($normalized_required_consents);

        $this->assertEqualsCanonicalizing($original_required_consents, $denormalized_required_consents);
    }

    public function testA(): void
    {
        function load_json_conditions(): string
        {
            return '{"type":"all","data":[{"type":"least_one","data":[1,11]},{"type":"least_one","data":[2,12]},3,4,{"type":"least_one","data":[5,15,25]}]}';
        }

        function get_consents(int $phone): array
        {
            return match ($phone) {
                1234567890 => [1, 12, 3, 4, 25, 8, 80],
                9876543210 => [11, 2, 3, 4, 5],
                9998887777 => [1, 2, 3, 11],
                default => [],
            };
        }

        $json_conditions = load_json_conditions();
        $normalizer      = new Normalizer();
        $conditions      = $normalizer->denormalize(json_decode($json_conditions, true));

        $this->assertTrue($conditions->check(get_consents(1234567890)));
        $this->assertTrue($conditions->check(get_consents(9876543210)));
        $this->assertFalse($conditions->check(get_consents(9998887777)));
        $this->assertFalse($conditions->check(get_consents(1112223333)));
    }
}
