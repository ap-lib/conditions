<?php

namespace AP\Conditions;

/**
 * Ensures that all elements exist in the original array before passing the condition.
 */
readonly class All extends Base
{
    protected function checkPrepared(PreparedElements $elements): bool
    {
        foreach ($this->index as $hash) {
            if (!key_exists($hash, $elements->hashmap)) {
                return false;
            }
        }

        foreach ($this->nested as $condition) {
            if (!$condition->checkPrepared($elements)) {
                return false;
            }
        }

        return true;
    }
}