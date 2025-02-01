<?php

namespace AP\Conditions;

/**
 * Ensures that at least one element exist in the original array before passing the condition.
 */
readonly class LeastOne extends Base
{
    protected function checkPrepared(PreparedElements $elements): bool
    {
        foreach ($this->index as $hash) {
            if (key_exists($hash, $elements->hashmap)) {
                return true;
            }
        }

        foreach ($this->nested as $condition) {
            if ($condition->checkPrepared($elements)) {
                return true;
            }
        }

        return false;
    }
}