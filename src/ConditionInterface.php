<?php

namespace AP\Conditions;

interface ConditionInterface
{
    /**
     * For performance reasons, it's beneficial to store element conditions in a hashmap.
     *
     * To optimize nested checks, the code processes input data once and prepares a hashmap
     * for lookups. This preprocessed data is then reused across all nested elements,
     * improving efficiency.
     */
    public function check(array|PreparedElements $elements): bool;

    /**
     * Retrieves all used options recursively.
     *
     * This method recursively retrieves all options used if other conditions are nested.
     * It is particularly useful for validating all options after normalizing
     * the data received from the user.
     *
     * @return array
     */
    public function getAllOptionsRecursive(): array;

    /**
     * @return array
     */
    public function getElements(): array;
}