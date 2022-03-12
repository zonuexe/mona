<?php

declare(strict_types=1);

namespace zonuexe\Mona;

/**
 * @extends MonadTest<MaybeMonad>
 */
class MaybeMonadTest extends MonadTest
{
    /**
     * @return iterable<array{MaybeMonad<string>}>
     */
    public function monadsProvider(): iterable
    {
        yield 'just' => [MaybeMonad\Just::unit('Monad')];
        yield 'nothing' => [MaybeMonad\Nothing::unit('Monad')];
    }
}
