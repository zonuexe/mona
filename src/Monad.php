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
     * @template T2
     * @param Closure(T):static<T2> $f
     * @return static<T2>
     */
    public function bind(Closure $f): Monad;
}
