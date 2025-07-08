<?php

namespace dayemsiddiqui\Langfuse\Testing;

use dayemsiddiqui\Langfuse\Langfuse;
use dayemsiddiqui\Langfuse\PromptBuilder;

class FakeLangfuse extends Langfuse
{
    public function __construct()
    {
        parent::__construct();
        $this->markAsFake();
    }

    protected array $prompts = [];

    protected array $requestHistory = [];

    protected bool $shouldThrowOnMissing = false;

    /**
     * Add a prompt to the fake
     */
    public function addPrompt(string $name, string $content): self
    {
        $this->prompts[$name] = $content;

        return $this;
    }

    /**
     * Add multiple prompts at once
     */
    public function addPrompts(array $prompts): self
    {
        foreach ($prompts as $name => $content) {
            $this->addPrompt($name, $content);
        }

        return $this;
    }

    /**
     * Configure the fake to throw exceptions for missing prompts
     */
    public function throwOnMissing(bool $shouldThrow = true): self
    {
        $this->shouldThrowOnMissing = $shouldThrow;

        return $this;
    }

    /**
     * Get a prompt from the fake
     */
    public function getPrompt(string $promptName): PromptBuilder
    {
        $this->recordRequest($promptName);

        if (! isset($this->prompts[$promptName])) {
            if ($this->shouldThrowOnMissing) {
                throw new \Exception("Prompt '{$promptName}' not found in fake");
            }

            return new PromptBuilder($promptName, '');
        }

        return new PromptBuilder($promptName, $this->prompts[$promptName]);
    }

    /**
     * Record a request for assertion purposes
     */
    protected function recordRequest(string $promptName): void
    {
        $this->requestHistory[] = [
            'prompt_name' => $promptName,
            'timestamp' => now(),
        ];
    }

    /**
     * Assert that a specific prompt was requested
     */
    public function assertPromptRequested(string $promptName): self
    {
        $requested = collect($this->requestHistory)
            ->pluck('prompt_name')
            ->contains($promptName);

        if (! $requested) {
            throw new \Exception("Expected prompt '{$promptName}' to be requested, but it was not.");
        }

        return $this;
    }

    /**
     * Assert that a specific prompt was requested a certain number of times
     */
    public function assertPromptRequestedTimes(string $promptName, int $times): self
    {
        $count = collect($this->requestHistory)
            ->pluck('prompt_name')
            ->filter(fn ($name) => $name === $promptName)
            ->count();

        if ($count !== $times) {
            throw new \Exception("Expected prompt '{$promptName}' to be requested {$times} times, but it was requested {$count} times.");
        }

        return $this;
    }

    /**
     * Assert that no prompts were requested
     */
    public function assertNoPromptsRequested(): self
    {
        if (! empty($this->requestHistory)) {
            $count = count($this->requestHistory);
            throw new \Exception("Expected no prompts to be requested, but {$count} were requested.");
        }

        return $this;
    }

    /**
     * Get all recorded requests
     */
    public function getRequestHistory(): array
    {
        return $this->requestHistory;
    }

    /**
     * Clear the request history
     */
    public function clearRequestHistory(): self
    {
        $this->requestHistory = [];

        return $this;
    }

    /**
     * Get all available prompts in the fake
     */
    public function getAvailablePrompts(): array
    {
        return array_keys($this->prompts);
    }
}
