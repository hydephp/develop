<?php

/**
 * @template T
 *
 * @param  class-string<T>  $abstract
 * @return T
 */
function app(?string $abstract = null, array $parameters = [])
{
}

/**
 * @template T
 *
 * @param  T  $value
 * @param  (callable(T): mixed)|null  $callback
 * @return ($callback is null ? HigherOrderTapProxy<T> : T)
 *
 * @psalm-assert-if-true !null $callback
 *
 * @psalm-suppress ImplicitToStringCast
 */
function tap($value, $callback = null)
{
}

/**
 * @template T
 */
class HigherOrderTapProxy
{
    /**
     * @param  T  $target
     */
    public function __construct($target)
    {
    }

    /**
     * @param  string  $method
     * @param  array  $parameters
     * @return T
     *
     * @psalm-suppress MixedInferredReturnType, MixedReturnStatement
     */
    public function __call($method, $parameters)
    {
    }
}
