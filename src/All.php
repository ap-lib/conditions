<?php

namespace AP\Conditions;

readonly class All extends Base
{
    /**
     * @param array<string,int> $hashmap
     * key   = <string> self::make_hashmap_key(value),
     * value = <int> elements count on the original list
     * @return bool
     */
    public function check_hashmap(array $hashmap): bool
    {
        foreach ($this->index as $hash) {
            if (!key_exists($hash, $hashmap)) {
                return false;
            }
        }

        foreach ($this->nested as $condition) {
            if (!$condition->check_hashmap($hashmap)) {
                return false;
            }
        }

        return true;
    }
}