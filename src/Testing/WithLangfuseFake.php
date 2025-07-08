<?php

namespace dayemsiddiqui\Langfuse\Testing;

trait WithLangfuseFake
{
    protected ?FakeLangfuse $langfuseFake = null;

    /**
     * Setup the fake before each test
     */
    protected function setUpLangfuseFake(): void
    {
        $this->langfuseFake = LangfuseFake::fake();
    }

    /**
     * Clean up after each test
     */
    protected function tearDownLangfuseFake(): void
    {
        if ($this->langfuseFake) {
            $this->langfuseFake->clearRequestHistory();
        }
        LangfuseFake::restore();
    }

    /**
     * Get the current fake instance
     */
    protected function getLangfuseFake(): FakeLangfuse
    {
        if (! $this->langfuseFake) {
            $this->setUpLangfuseFake();
        }

        return $this->langfuseFake;
    }
}
