<?php

/**
 * @template T
 *
 * @param  T  $value
 * @param  callable(?T): mixed  $callback
 * @return T
 */
function tap($value, $callback = null)
{
}

class HigherOrderTapProxy
{
    /**
     * @template T
     *
     * @param  T  $target
     */
    public function __construct($target)
    {
    }

    /**
     * @template T
     *
     * @param  T  $target
     * @param  string  $method
     * @param  array  $parameters
     * @return T
     */
    public function __call($method, $parameters)
    {
    }
}
