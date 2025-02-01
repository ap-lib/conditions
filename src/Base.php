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
                $index[$k] = PreparedElements::makeHashmapKey($el);
            }
        }
        $this->index  = $index;
        $this->nested = $nested;
    }

    public function getElements(): array
    {
        return $this->elements;
    }

    public function getAllOptionsRecursive(): array
    {
        $res = [];
        foreach ($this->elements as $el) {
            if ($el instanceof ConditionInterface) {
                foreach ($el->getAllOptionsRecursive() as $k => $v) {
                    $res[] = $v;
                }
            } else {
                $res[] = $el;
            }
        }
        return array_unique($res);
    }


    public function check(array|PreparedElements $elements): bool
    {
        return $this->checkPrepared($elements instanceof PreparedElements ?
            $elements :
            new PreparedElements($elements)
        );
    }

    abstract protected function checkPrepared(PreparedElements $elements): bool;
}