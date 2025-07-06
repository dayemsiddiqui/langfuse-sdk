<?php

namespace dayemsiddiqui\Langfuse\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \dayemsiddiqui\Langfuse\Langfuse
 */
class Langfuse extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \dayemsiddiqui\Langfuse\Langfuse::class;
    }
}
