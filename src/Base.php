<?php

namespace AP\Conditions;

readonly abstract class Base implements ConditionInterface
{
    /**
     * @var array<string>
     */
    protected array $index;

    /**
     * @var array<ConditionInterface>
     */
    protected array $nested;

    /**
     * @param array $elements
     */
    public function __construct(private array $elements)
    {
        $index  = [];
        $nested = [];

        foreach ($this->elements as $k => $el) {
            if ($el instanceof ConditionInterface) {
                $nested[$k] = $el;
            } else {
                $index[$k] = self::make_hashmap_key($el);
            }
        }
        $this->index  = $index;
        $this->nested = $nested;
    }

    public function get_elements(): array
    {
        return $this->elements;
    }

    public function get_all_options_recursive(): array
    {
        $res = [];
        foreach ($this->elements as $el) {
            if ($el instanceof ConditionInterface) {
                foreach ($el->get_all_options_recursive() as $k => $v) {
                    $res[] = $v;
                }
            } else {
                $res[] = $el;
            }
        }
        return array_unique($res);
    }

    public static function make_hashmap_key(mixed $element): string
    {
        return serialize($element);
    }

    public function check(array $elements): bool
    {
        $hashmap = [];
        foreach ($elements as $el) {
            $hash = self::make_hashmap_key($el);
            if (!isset($hashmap[$hash])) {
                $hashmap[self::make_hashmap_key($el)] = 1;
            } else {
                $hashmap[$hash]++;
            }
        }
        return $this->check_hashmap($hashmap);
    }

    /**
     * @param array<string,int> $hashmap
     *          key   = <string> self::make_hashmap_key(value),
     *          value = <int> elements count on the original list
     * @return bool
     */
    abstract public function check_hashmap(array $hashmap): bool;
}