<?php

declare(strict_types=1);

namespace zonuexe\Mona\MaybeMonad;

use Closure;
use zonuexe\Mona\MaybeMonad;

/**
 * @template T
 * @extends MaybeMonad<T>
 */
final class Just extends MaybeMonad
{
    public function __construct(
        /** @var T */
        protected mixed $value
    ) {
    }

    /**
     * @param Closure(T):static<T> $f
     * @return static<T>
     */
    public function bind(Closure $f): self
    {
        return $f($this->value);
    }
}
