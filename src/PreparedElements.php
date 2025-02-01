<?php

namespace AP\Conditions;

/**
 * For performance reasons, it's beneficial to store element conditions in a hashmaps
 *
 * To optimize nested checks, the code processes input data once and prepares a hashmap
 * for lookups. This preprocessed data is then reused across all nested elements,
 * improving efficiency.
 *
 * @return bool
 */
readonly class PreparedElements
{
    public static function makeHashmapKey(mixed $element): string
    {
        return serialize($element);
    }

    /**
     * @var array<string,int> $hashmap
     *   key: <string> PreparedElements::make_hashmap_key(value),
     *   value: <int> elements count on the original list
     */
    public array $hashmap;

    /**
     * @param array $elements
     */
    public function __construct(public array $elements)
    {
        $hashmap = [];
        foreach ($elements as $el) {
            $hash = self::makeHashmapKey($el);
            if (!isset($hashmap[$hash])) {
                $hashmap[self::makeHashmapKey($el)] = 1;
            } else {
                $hashmap[$hash]++;
            }
        }
        $this->hashmap = $hashmap;
    }
}