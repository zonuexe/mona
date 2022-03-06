<?php

declare(strict_types=1);

namespace zonuexe\Mona;

use function zonuexe\Mona\_return;

/**
 * @extends MonadTest<ListMonad>
 */
class ListMonadTest extends MonadTest
{
    public function getSubject(): Monad
    {
        return ListMonad::unit('Monad');
    }
}
