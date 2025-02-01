<?php

namespace AP\Conditions;

use UnexpectedValueException;

/**
 * Enforces minimum and maximum constraints on element occurrences.
 * - Validates `min` and `max` values to prevent invalid ranges.
 * - Optionally ignores duplicate elements.
 */
readonly class Limit extends Base
{
    public ?int $min;

    public function __construct(
        array       $elements,
        ?int        $min = null,
        public ?int $max = null,
        public bool $ignore_duplicates = false,
    )
    {
        parent::__construct($elements);
        if (is_int($min) && $min < 0) {
            throw new UnexpectedValueException("min must be equal or great then zero");
        }

        if (is_int($this->max) && $this->max < 1) {
            throw new UnexpectedValueException("max must be equal or great then one");
        }

        if (is_int($this->max) && is_int($min) && $min > $this->max) {
            throw new UnexpectedValueException("min must be equal or less then max");
        }

        if ($min == 0) {
            $min = null;
        }

        $this->min = $min;
    }

    protected function checkPrepared(PreparedElements $elements): bool
    {
        if (!is_int($this->max) && !is_int($this->min)) {
            return true;
        }

        $exist = 0;

        foreach ($this->index as $hash) {
            if (key_exists($hash, $elements->hashmap)) {
                $exist += $this->ignore_duplicates ? 1 : $elements->hashmap[$hash];
                // by performance reason, check after iteration code, copy twice
                if (is_int($this->max) && $exist > $this->max) {
                    return false;
                }
                if (!is_int($this->max) && $exist >= $this->min) {
                    return true;
                }
            }
        }

        foreach ($this->nested as $condition) {
            if ($condition->checkPrepared($elements)) {
                $exist++;
                // by performance reason, check after iteration code, copy twice
                if (is_int($this->max) && $exist > $this->max) {
                    return false;
                }
                if (!is_int($this->max) && $exist >= $this->min) {
                    return true;
                }
            }
        }

        if (is_int($this->min) && $exist < $this->min) {
            return false;
        }

        return true;
    }
}