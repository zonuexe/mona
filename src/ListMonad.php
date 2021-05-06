<?php

declare(strict_types=1);

namespace zonuexe\Mona;

use Closure;
use function zonuexe\Mona\ret;

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
    private function __construct($v)
    {
        $this->v = $v;
    }

    /**
     * @param T $v
     * @return self<T>
     */
    public static function new($v): self
    {
        return new self($v);
    }

    /**
     * @param Closure(T):T $f
     * @return self<T>
     */
    public function bind(Closure $f): Monad
    {
        return ret($f($this->v), ListMonad::class);
    }
}
