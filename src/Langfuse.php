<?php

namespace dayemsiddiqui\Langfuse;

use dayemsiddiqui\Langfuse\Exceptions\MissingPromptVariablesException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class Langfuse
{
    protected string $publicKey;

    protected string $secretKey;

    protected string $host;

    public function __construct()
    {
        $this->publicKey = config('langfuse-sdk.public_key');
        $this->secretKey = config('langfuse-sdk.secret_key');
        $this->host = config('langfuse-sdk.host');
    }

    public function getPrompt(string $promptName, array $variables = []): string
    {
        try {
            $response = Http::withBasicAuth($this->publicKey, $this->secretKey)
                ->get("{$this->host}/api/public/v2/prompts/{$promptName}");

            $response->throw();

            $promptData = $response->json();

            // Process the prompt template for variables
            if (isset($promptData['prompt'])) {
                $promptData['prompt'] = $this->processPromptVariables($promptData['prompt'], $variables, $promptName);
            }

            return $promptData['prompt'];
        } catch (RequestException $e) {
            throw new \Exception("Failed to fetch prompt '{$promptName}': ".$e->getMessage());
        }
    }

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

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    public function getHost(): string
    {
        return $this->host;
    }
}
