<?php

declare(strict_types=1);

namespace zonuexe\Mona\MaybeMonad;

use Closure;
use zonuexe\Mona\MaybeMonad;

/**
 * @template T
 * @extends MaybeMonad<T>
 */
final class Nothing extends MaybeMonad
{
    /**
     * @phpstan-param T $value
     */
    public function __construct(mixed $value) // @phpstan-ignore-line
    {
    }

    /**
     * @template T2
     * @param Closure(T):static<T2> $f
     * @return static<T2>
     */
    public function bind(Closure $f): self
    {
        return $this;
    }

    /**
     * @template TValue
     * @param TValue $value
     * @return Nothing<TValue>
     */
    public static function unit(mixed $value): self
    {
        return new Nothing($value);
    }
}
