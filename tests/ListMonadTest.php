<?php

declare(strict_types=1);

namespace zonuexe\Mona;

/**
 * @extends MonadTest<ListMonad>
 */
class ListMonadTest extends MonadTest
{
    public function monadsProvider(): iterable
    {
        /** @var ListMonad<string> */
        $nil = ListMonad::nil();

        yield 'unit' => [ListMonad::unit('Monad')];
        yield 'list' => [ListMonad::list('1', '2', '3')];
        yield 'nil' => [$nil];
    }
}
