<?php

namespace AP\Conditions;

interface ConditionInterface
{
    public function check(array $elements): bool;

    /**
     * @param array<string,int> $hashmap
     * @return bool
     */
    public function check_hashmap(array $hashmap): bool;

    /**
     * Retrieves all used options recursively.
     *
     * This method recursively retrieves all options used if other conditions are nested.
     * It is particularly useful for validating all options after normalizing
     * the data received from the user.
     *
     * @return array
     */
    public function get_all_options_recursive(): array;

    /**
     * @return array
     */
    public function get_elements(): array;
}