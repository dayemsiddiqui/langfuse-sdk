<?php

namespace dayemsiddiqui\Langfuse;

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

    public function getPrompt(string $promptName, array $variables = []): array
    {
        try {
            $response = Http::withBasicAuth($this->publicKey, $this->secretKey)
                ->get("{$this->host}/api/public/v2/prompts/{$promptName}");

            $response->throw();

            $promptData = $response->json();

            // If variables are provided, you might want to process the prompt template
            if (! empty($variables) && isset($promptData['prompt'])) {
                dd($promptData['prompt'], $variables);
                $promptData['prompt'] = $this->processPromptVariables($promptData['prompt'], $variables);
            }

            return $promptData['prompt'];
        } catch (RequestException $e) {
            throw new \Exception("Failed to fetch prompt '{$promptName}': ".$e->getMessage());
        }
    }

    protected function processPromptVariables(string $prompt, array $variables): string
    {
        // Simple variable replacement - you might want to use a more sophisticated template engine
        foreach ($variables as $key => $value) {
            $prompt = str_replace('{{'.$key.'}}', $value, $prompt);
        }

        return $prompt;
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
