<?php

namespace dayemsiddiqui\Langfuse\Testing;

use dayemsiddiqui\Langfuse\Langfuse;
use Illuminate\Support\Facades\Facade;

class LangfuseFake extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'langfuse.fake';
    }

    /**
     * Create a fake instance of Langfuse
     */
    public static function fake(array $prompts = []): FakeLangfuse
    {
        $fake = new FakeLangfuse;

        if (! empty($prompts)) {
            $fake->addPrompts($prompts);
        }

        app()->instance('langfuse.fake', $fake);
        app()->instance(Langfuse::class, $fake);

        return $fake;
    }

    /**
     * Get the current fake instance
     */
    public static function getFake(): ?FakeLangfuse
    {
        return app('langfuse.fake');
    }

    /**
     * Restore the real Langfuse instance
     */
    public static function restore(): void
    {
        app()->forgetInstance('langfuse.fake');
        app()->forgetInstance(Langfuse::class);
    }
}
