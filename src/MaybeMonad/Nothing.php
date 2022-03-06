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
    public function __construct()
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
}
