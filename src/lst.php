<?php

declare(strict_types=1);

namespace zonuexe\Mona\lst;

use Closure;
use zonuexe\Mona\ListMonad;

use function zonuexe\Mona\_return;

/**
 * @template T
 * @param Closure(T,T):T $f
 * @param T $z
 * @param ListMonad<T> $xs
 * @return T
 */
function foldl(Closure $f, $z, ListMonad $xs)
{
    $succ = $z;
    foreach ($xs as $x) {
        $succ = $f($succ, $x);
    }

    return $succ;
}

/**
 * @template T
 * @param Closure(T,ListMonad<T>):T $f
 * @param T $z
 * @param ListMonad<T> $xs
 * @return T
 */
function foldr(Closure $f, $z, ListMonad $xs)
{
    return $xs->foldr($f, $z);
}
