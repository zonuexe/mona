<?php

declare(strict_types=1);

namespace zonuexe\Mona;

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
}
