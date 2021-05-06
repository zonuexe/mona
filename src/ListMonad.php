<?php

declare(strict_types=1);

namespace zonuexe\Mona;

use Closure;

/**
 * @template T
 * @implements Monad<T>
 */
class ListMonad implements Monad
{
    /** @var T */
    private $v;

    /**
     * @param T $v
     */
    public function __construct($v)
    {
        $this->v = $v;
    }

    /**
     * @param Closure(T):static<T> $f
     * @return static<T>
     */
    public function bind(Closure $f): Monad
    {
        return $f($this->v);
    }
}
