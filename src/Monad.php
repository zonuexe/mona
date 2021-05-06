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
     * @param T $v
     * @return Monad<T>
     */
    public static function new($v): Monad;

    /**
     * @param Closure(T):T $f
     * @return static<T>
     */
    public function bind(Closure $f): Monad;
}
