<?php

namespace dayemsiddiqui\Langfuse;

use dayemsiddiqui\Langfuse\Exceptions\MissingPromptVariablesException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use dayemsiddiqui\Langfuse\PromptBuilder;


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

    /**
     * Get a prompt from Langfuse that can be chained with compile() or used as raw
     */
    public function getPrompt(string $promptName): PromptBuilder
    {
        try {
            $response = Http::withBasicAuth($this->publicKey, $this->secretKey)
                ->get("{$this->host}/api/public/v2/prompts/{$promptName}");

            $response->throw();

            $promptData = $response->json();

            return new PromptBuilder($promptName, $promptData['prompt'] ?? '');
        } catch (RequestException $e) {
            throw new \Exception("Failed to fetch prompt '{$promptName}': ".$e->getMessage());
        }
    }

    /**
     * Legacy method for backward compatibility - directly get compiled prompt
     * 
     * @deprecated Use getPrompt($promptName)->compile($variables) instead
     */
    public function getCompiledPrompt(string $promptName, array $variables = []): string
    {
        return $this->getPrompt($promptName)->compile($variables);
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
