<?php

namespace AP\Conditions;

use Closure;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

readonly class Normalizer
{
    private array $conditions_hashmap;

    /**
     * @param array<string, class-string<ConditionInterface>> $conditions
     * @param string|int $type
     * @param string|int $data
     * @param string|array|Closure|null $element_normalizer
     * @param string|array|Closure|null $element_denormalizer
     * @throws ReflectionException
     */
    public function __construct(
        public array                     $conditions = [
            "all"       => All::class,
            "least_one" => LeastOne::class,
            "limit"     => Limit::class,
        ],
        public string|int                $type = 'type',
        public string|int                $data = 'data',
        public string|array|Closure|null $element_normalizer = null,
        public string|array|Closure|null $element_denormalizer = null,
    )
    {
        if ((string)$type == (string)$data) {
            throw new InvalidArgumentException("`type` can not be equal `data`");
        }

        if (!is_null($element_normalizer) && !is_callable($element_normalizer)) {
            throw new InvalidArgumentException(
                "`element_normalizer` must be null or is_callable"
            );
        }

        if (!is_null($element_denormalizer) && !is_callable($element_denormalizer)) {
            throw new InvalidArgumentException(
                "`element_denormalizer` must be null or is_callable"
            );
        }

        $conditions_hashmap = [];
        foreach ($conditions as $name => $class) {
            if (!(new ReflectionClass($class))->implementsInterface(ConditionInterface::class)) {
                throw new InvalidArgumentException(
                    "class `$class` no implement interface `" . ConditionInterface::class . "`"
                );
            }
            if (isset($conditions_hashmap[$class])) {
                throw new InvalidArgumentException(
                    "duplicate mapping `$name` and `$conditions_hashmap[$class]` for  class `$class`"
                );
            } else {
                $conditions_hashmap[$class] = $name;
            }
        }
        $this->conditions_hashmap = $conditions_hashmap;
    }

    private function validate_normalized_elements_exception(mixed $element, bool $root = true): mixed
    {
        if (is_int($element) || is_string($element) || is_bool($element)) {
            return $element;
        }
        if (is_array($element)) {
            if ($root && isset($element[$this->data]) && isset($element[$this->type])) {
                throw new RuntimeException();
            }

            foreach ($element as $v) {
                $this->validate_normalized_elements_exception($v, false);
            }
            return $element;
        }

        throw new RuntimeException(
            "Element normalization failed: all elements must be recursively converted to " .
            "one of the following types: int, string, or array."
        );
    }

    public function normalize(ConditionInterface $condition)
    {
        $class = $condition::class;
        if (!isset($this->conditions_hashmap[$class])) {
            throw new RuntimeException(
                "no registered condition class `$class`, please set all possible condition classes " .
                "on the Normalizer constructor"
            );
        }

        $data = [];

        $element_normalizer = $this->element_normalizer;

        foreach ($condition->get_elements() as $element) {
            if ($element instanceof ConditionInterface) {
                $data[] = $this->normalize($element);
            } else {
                if (!is_null($element_normalizer)) {
                    $element = $element_normalizer($element);
                }

                $data[] = self::validate_normalized_elements_exception($element);
            }
        }

        return [
            $this->type => $this->conditions_hashmap[$class],
            $this->data => $data,
        ];
    }

    public function denormalize(array $data): ConditionInterface
    {
        if (!isset($data[$this->type]) || !isset($data[$this->data])) {
            throw new InvalidArgumentException(
                "The provided data must contain keys '{$this->type}' and '{$this->data}'."
            );
        }

        $type     = $data[$this->type];
        $elements = $data[$this->data];

        if (!isset($this->conditions[$type])) {
            throw new RuntimeException(
                "Unrecognized condition type '$type'. Ensure all condition types are registered in the Normalizer"
            );
        }

        $class = $this->conditions[$type];

        if (!is_array($elements)) {
            throw new InvalidArgumentException("The '{$this->data}' key must contain an array of elements.");
        }

        $element_denormalizer  = $this->element_denormalizer;
        $denormalized_elements = [];
        foreach ($elements as $element) {
            if (is_array($element) && isset($element[$this->type]) && isset($element[$this->data])) {
                $denormalized_elements[] = $this->denormalize($element);
            } else {
                if (!is_null($element_denormalizer)) {
                    $element = $element_denormalizer($element);
                }
                $denormalized_elements[] = $element;
            }
        }

        return new $class($denormalized_elements);
    }
}