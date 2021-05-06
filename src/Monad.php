<?php

declare(strict_types=1);

namespace zonuexe\Mona;

use Closure;

/**
 * @template T
 */
interface Monad
{
    /**
     * @param Closure(T):static<T> $f
     * @return static<T>
     */
    public function bind(Closure $f): Monad;
}
