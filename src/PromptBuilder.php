<?php

namespace dayemsiddiqui\Langfuse;

use dayemsiddiqui\Langfuse\Exceptions\MissingPromptVariablesException;

class PromptBuilder
{
    protected string $promptName;

    protected string $promptContent;

    public function __construct(string $promptName, string $promptContent)
    {
        $this->promptName = $promptName;
        $this->promptContent = $promptContent;
    }

    /**
     * Compile the prompt with provided variables
     */
    public function compile(array $variables): string
    {
        return $this->processPromptVariables($this->promptContent, $variables, $this->promptName);
    }

    /**
     * Get the raw prompt content without variable replacement
     */
    public function raw(): string
    {
        return $this->promptContent;
    }

    /**
     * Allow direct string conversion to get raw content
     */
    public function __toString(): string
    {
        return $this->raw();
    }

    /**
     * Get the prompt name
     */
    public function getPromptName(): string
    {
        return $this->promptName;
    }

    /**
     * Process prompt variables and replace them in the template
     */
    protected function processPromptVariables(string $prompt, array $variables, string $promptName = ''): string
    {
        // Validate that all required variables are provided
        $this->validatePromptVariables($prompt, $variables, $promptName);

        // Enhanced variable replacement that handles whitespace
        foreach ($variables as $key => $value) {
            // Replace both exact matches and matches with whitespace
            $prompt = preg_replace('/\{\{\s*'.preg_quote($key).'\s*\}\}/', $value, $prompt);
        }

        return $prompt;
    }

    /**
     * Validate that all required variables in the prompt are provided
     *
     * @param  string  $prompt  The prompt template content
     * @param  array  $variables  The variables provided by the user
     * @param  string  $promptName  The name of the prompt (for better error messages)
     *
     * @throws MissingPromptVariablesException When required variables are missing
     */
    protected function validatePromptVariables(string $prompt, array $variables, string $promptName = ''): void
    {
        // Extract all variables from the prompt using regex
        preg_match_all('/\{\{([^}]+)\}\}/', $prompt, $matches);
        $requiredVariables = $matches[1] ?? [];

        // Remove duplicates and trim whitespace
        $requiredVariables = array_unique(array_map('trim', $requiredVariables));

        // Check if all required variables are provided
        $missingVariables = [];
        foreach ($requiredVariables as $requiredVariable) {
            if (! array_key_exists($requiredVariable, $variables)) {
                $missingVariables[] = $requiredVariable;
            }
        }

        // Throw custom exception if any variables are missing
        if (! empty($missingVariables)) {
            throw new MissingPromptVariablesException(
                $missingVariables,
                $variables,
                $promptName,
                $prompt
            );
        }
    }
}
