<?php

use dayemsiddiqui\Langfuse\Exceptions\MissingPromptVariablesException;
use dayemsiddiqui\Langfuse\Langfuse;
use dayemsiddiqui\Langfuse\PromptBuilder;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'langfuse-sdk.public_key' => 'test-public-key',
        'langfuse-sdk.secret_key' => 'test-secret-key',
        'langfuse-sdk.host' => 'https://api.langfuse.com',
    ]);
});

it('can get prompt builder instance', function () {
    $mockPromptData = [
        'prompt' => 'Hello World!',
    ];

    Http::fake([
        'api.langfuse.com/api/public/v2/prompts/test-prompt' => Http::response($mockPromptData),
    ]);

    $langfuse = new Langfuse;
    $promptBuilder = $langfuse->getPrompt('test-prompt');

    expect($promptBuilder)->toBeInstanceOf(PromptBuilder::class);
    expect($promptBuilder->getPromptName())->toBe('test-prompt');
});

it('can get raw prompt content', function () {
    $mockPromptData = [
        'prompt' => 'Hello {{name}}! How are you today?',
    ];

    Http::fake([
        'api.langfuse.com/api/public/v2/prompts/test-prompt' => Http::response($mockPromptData),
    ]);

    $langfuse = new Langfuse;
    $result = $langfuse->getPrompt('test-prompt')->raw();

    expect($result)->toBe('Hello {{name}}! How are you today?');
});

it('can get raw prompt content using string conversion', function () {
    $mockPromptData = [
        'prompt' => 'Hello {{name}}! How are you today?',
    ];

    Http::fake([
        'api.langfuse.com/api/public/v2/prompts/test-prompt' => Http::response($mockPromptData),
    ]);

    $langfuse = new Langfuse;
    $result = (string) $langfuse->getPrompt('test-prompt');

    expect($result)->toBe('Hello {{name}}! How are you today?');
});

it('can compile prompt with variables', function () {
    $mockPromptData = [
        'prompt' => 'Hello {{name}}! How are you today?',
    ];

    Http::fake([
        'api.langfuse.com/api/public/v2/prompts/test-prompt' => Http::response($mockPromptData),
    ]);

    $langfuse = new Langfuse;
    $result = $langfuse->getPrompt('test-prompt')->compile(['name' => 'John']);

    expect($result)->toBe('Hello John! How are you today?');
});

it('can reuse prompt builder for multiple compilations', function () {
    $mockPromptData = [
        'prompt' => 'Hello {{name}}! How are you today?',
    ];

    Http::fake([
        'api.langfuse.com/api/public/v2/prompts/test-prompt' => Http::response($mockPromptData),
    ]);

    $langfuse = new Langfuse;
    $promptBuilder = $langfuse->getPrompt('test-prompt');

    $result1 = $promptBuilder->compile(['name' => 'John']);
    $result2 = $promptBuilder->compile(['name' => 'Jane']);

    expect($result1)->toBe('Hello John! How are you today?');
    expect($result2)->toBe('Hello Jane! How are you today?');
});

it('throws detailed exception when single required variable is missing', function () {
    $mockPromptData = [
        'prompt' => 'Hello {{name}}! Your email is {{email}}.',
    ];

    Http::fake([
        'api.langfuse.com/api/public/v2/prompts/test-prompt' => Http::response($mockPromptData),
    ]);

    $langfuse = new Langfuse;

    try {
        $langfuse->getPrompt('test-prompt')->compile(['name' => 'John']);
        expect(true)->toBeFalse('Expected MissingPromptVariablesException to be thrown');
    } catch (MissingPromptVariablesException $e) {
        expect($e->getMissingVariables())->toBe(['email']);
        expect($e->getProvidedVariables())->toBe(['name' => 'John']);
        expect($e->getPromptName())->toBe('test-prompt');
        expect($e->getPromptContent())->toBe('Hello {{name}}! Your email is {{email}}.');
        expect($e->getMessage())->toContain('Missing required variables for prompt \'test-prompt\': email');
        expect($e->getMessage())->toContain('Variables provided: name');
        expect($e->getMessage())->toContain('Solution: Please provide values for the missing variable when calling getPrompt()');
        expect($e->getMessage())->toContain('\'email\' => \'your_value_here\'');
    }
});

it('throws detailed exception when multiple required variables are missing', function () {
    $mockPromptData = [
        'prompt' => 'Hello {{name}}! Your email is {{email}} and your phone is {{phone}}.',
    ];

    Http::fake([
        'api.langfuse.com/api/public/v2/prompts/test-prompt' => Http::response($mockPromptData),
    ]);

    $langfuse = new Langfuse;

    try {
        $langfuse->getPrompt('test-prompt')->compile([]);
        expect(true)->toBeFalse('Expected MissingPromptVariablesException to be thrown');
    } catch (MissingPromptVariablesException $e) {
        expect($e->getMissingVariables())->toBe(['name', 'email', 'phone']);
        expect($e->getProvidedVariables())->toBe([]);
        expect($e->getPromptName())->toBe('test-prompt');
        expect($e->getMessage())->toContain('Missing required variables for prompt \'test-prompt\': name, email, phone');
        expect($e->getMessage())->toContain('Variables provided: none');
        expect($e->getMessage())->toContain('Solution: Please provide values for the missing variables when calling getPrompt()');
        expect($e->getMessage())->toContain('\'name\' => \'your_value_here\'');
        expect($e->getMessage())->toContain('\'email\' => \'your_value_here\'');
        expect($e->getMessage())->toContain('\'phone\' => \'your_value_here\'');
    }
});

it('throws detailed exception with partial variables provided', function () {
    $mockPromptData = [
        'prompt' => 'Hello {{name}}! Your email is {{email}} and your phone is {{phone}}.',
    ];

    Http::fake([
        'api.langfuse.com/api/public/v2/prompts/test-prompt' => Http::response($mockPromptData),
    ]);

    $langfuse = new Langfuse;

    try {
        $langfuse->getPrompt('test-prompt')->compile(['name' => 'John', 'email' => 'john@example.com']);
        expect(true)->toBeFalse('Expected MissingPromptVariablesException to be thrown');
    } catch (MissingPromptVariablesException $e) {
        expect($e->getMissingVariables())->toBe(['phone']);
        expect($e->getProvidedVariables())->toBe(['name' => 'John', 'email' => 'john@example.com']);
        expect($e->getMessage())->toContain('Missing required variables for prompt \'test-prompt\': phone');
        expect($e->getMessage())->toContain('Variables provided: name, email');
        expect($e->getMessage())->toContain('\'name\' => \'John\'');
        expect($e->getMessage())->toContain('\'email\' => \'john@example.com\'');
        expect($e->getMessage())->toContain('\'phone\' => \'your_value_here\'');
    }
});

it('works with extra variables provided', function () {
    $mockPromptData = [
        'prompt' => 'Hello {{name}}!',
    ];

    Http::fake([
        'api.langfuse.com/api/public/v2/prompts/test-prompt' => Http::response($mockPromptData),
    ]);

    $langfuse = new Langfuse;
    $result = $langfuse->getPrompt('test-prompt')->compile([
        'name' => 'John',
        'extra' => 'value',
    ]);

    expect($result)->toBe('Hello John!');
});

it('handles prompts with no variables correctly', function () {
    $mockPromptData = [
        'prompt' => 'This is a static prompt without variables.',
    ];

    Http::fake([
        'api.langfuse.com/api/public/v2/prompts/test-prompt' => Http::response($mockPromptData),
    ]);

    $langfuse = new Langfuse;
    $result = $langfuse->getPrompt('test-prompt')->compile(['unused' => 'variable']);

    expect($result)->toBe('This is a static prompt without variables.');
});

it('handles variables with whitespace correctly', function () {
    $mockPromptData = [
        'prompt' => 'Hello {{ name }}! Welcome to {{ company }}.',
    ];

    Http::fake([
        'api.langfuse.com/api/public/v2/prompts/test-prompt' => Http::response($mockPromptData),
    ]);

    $langfuse = new Langfuse;
    $result = $langfuse->getPrompt('test-prompt')->compile([
        'name' => 'John',
        'company' => 'TechCorp',
    ]);

    expect($result)->toBe('Hello John! Welcome to TechCorp.');
});

it('handles duplicate variables correctly', function () {
    $mockPromptData = [
        'prompt' => 'Hello {{name}}! Nice to meet you, {{name}}.',
    ];

    Http::fake([
        'api.langfuse.com/api/public/v2/prompts/test-prompt' => Http::response($mockPromptData),
    ]);

    $langfuse = new Langfuse;
    $result = $langfuse->getPrompt('test-prompt')->compile(['name' => 'John']);

    expect($result)->toBe('Hello John! Nice to meet you, John.');
});

it('can get configuration values', function () {
    $langfuse = new Langfuse;

    expect($langfuse->getPublicKey())->toBe('test-public-key');
    expect($langfuse->getSecretKey())->toBe('test-secret-key');
    expect($langfuse->getHost())->toBe('https://api.langfuse.com');
});

// Backward compatibility tests
it('supports legacy getCompiledPrompt method', function () {
    $mockPromptData = [
        'prompt' => 'Hello {{name}}! How are you today?',
    ];

    Http::fake([
        'api.langfuse.com/api/public/v2/prompts/test-prompt' => Http::response($mockPromptData),
    ]);

    $langfuse = new Langfuse;
    $result = $langfuse->getCompiledPrompt('test-prompt', ['name' => 'John']);

    expect($result)->toBe('Hello John! How are you today?');
});

it('legacy getCompiledPrompt method works without variables', function () {
    $mockPromptData = [
        'prompt' => 'Hello World!',
    ];

    Http::fake([
        'api.langfuse.com/api/public/v2/prompts/test-prompt' => Http::response($mockPromptData),
    ]);

    $langfuse = new Langfuse;
    $result = $langfuse->getCompiledPrompt('test-prompt');

    expect($result)->toBe('Hello World!');
});
