<?php

namespace App\Support;

/**
 * @template T
 */
class Lazy
{
    /**
     * @param \Closure<T> $callback
     */
    public function __construct(private readonly \Closure $callback)
    {
    }

    /**
     * @return T
     */
    public function value()
    {
        $callback = $this->callback;
        return $callback();
    }
}
