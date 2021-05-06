<?php

declare(strict_types=1);

namespace zonuexe\Mona;

use Closure;

/**
 * @template T
 * @template M of Monad
 * @param T $v
 * @param class-string<M> $monad
 * @return M<T>
 */
function _return($v, string $monad): Monad
{
    return new $monad($v);
}

/**
 * @template T
 * @template M of Monad<T>
 * @param M $m
 * @param Closure(T):T $f
 * @return M<T>
 */
function bind(Monad $m, Closure $f): Monad
{
    return $m->bind($f);
}
