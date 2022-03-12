<?php

declare(strict_types=1);

namespace zonuexe\Mona;

use zonuexe\Mona\Monad;
use function get_class;
use function zonuexe\Mona\_return;

/**
 * @template TMonad of Monad
 */
abstract class MonadTest extends TestCase
{
    /**
     * @return iterable<array{TMonad<string>}>
     */
    abstract public function monadsProvider(): iterable;

    /**
     * Test Monad laws
     *
     * @dataProvider monadsProvider
     * @param TMonad<string> $subject
     * @see https://wiki.haskell.org/Monad_laws
     */
    public function testMonadLaws(Monad $subject): void
    {
        $monad = get_class($subject);

        /** @var \Closure(string):TMonad<string> $return */
        $return = fn($v) => _return($v, $monad);

        $f = fn(string $n): Monad => _return($n . 1, $monad);
        $g = fn(string $n): Monad => _return($n . 2, $monad);

        $this->assertEquals(
            $monad::unit('x')->bind($f),
            $f('x'),
            'Monad law - Left identity (return x >>= f == f x)'
        );
        $this->assertEquals(
            $subject->bind($return),
            $subject,
            'Monad law - Right identity (m >>= return == m)'
        );
        $this->assertEquals(
            $subject->bind($f)->bind($g),
            $subject->bind(fn($x) => $f($x)->bind($g)),
            'Monad law - Associativity (m >>= f) >>= g == m >>= (\x -> f x >>= g)'
        );
    }
}
