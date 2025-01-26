<?php

namespace AP\Conditions;

readonly class LeastOne extends Base
{
    public function check_hashmap(array $hashmap): bool
    {
        foreach ($this->index as $hash) {
            if (key_exists($hash, $hashmap)) {
                return true;
            }
        }

        foreach ($this->nested as $condition) {
            if ($condition->check_hashmap($hashmap)) {
                return true;
            }
        }

        return false;
    }
}