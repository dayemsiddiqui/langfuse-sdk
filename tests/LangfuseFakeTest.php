<?php

use dayemsiddiqui\Langfuse\Langfuse;
use dayemsiddiqui\Langfuse\Testing\LangfuseFake;
use dayemsiddiqui\Langfuse\Testing\WithLangfuseFake;

uses(WithLangfuseFake::class);

beforeEach(function () {
    config([
        'langfuse-sdk.public_key' => 'test-public-key',
        'langfuse-sdk.secret_key' => 'test-secret-key',
        'langfuse-sdk.host' => 'https://api.langfuse.com',
    ]);
});

it('can fake prompts elegantly', function () {
    // Setup fake with prompts
    LangfuseFake::fake([
        'greeting' => 'Hello {{name}}!',
        'welcome' => 'Welcome to {{platform}}, {{name}}!',
    ]);

    $langfuse = app(Langfuse::class);

    // Test the fake works
    $result = $langfuse->getPrompt('greeting')->compile(['name' => 'John']);
    expect($result)->toBe('Hello John!');

    // Assert the prompt was requested
    LangfuseFake::getFake()->assertPromptRequested('greeting');
});

it('can chain fake methods elegantly', function () {
    LangfuseFake::fake()
        ->addPrompt('greeting', 'Hello {{name}}!')
        ->addPrompt('farewell', 'Goodbye {{name}}!')
        ->throwOnMissing();

    $langfuse = app(Langfuse::class);

    $result = $langfuse->getPrompt('greeting')->compile(['name' => 'John']);
    expect($result)->toBe('Hello John!');

    // Assert multiple requests
    LangfuseFake::getFake()
        ->assertPromptRequested('greeting')
        ->assertPromptRequestedTimes('greeting', 1);
});

it('can test complex scenarios', function () {
    LangfuseFake::fake([
        'user-welcome' => 'Welcome {{name}}! Your role is {{role}}.',
        'admin-dashboard' => 'Admin dashboard for {{name}}',
    ]);

    $langfuse = app(Langfuse::class);

    // Simulate user flow
    $welcome = $langfuse->getPrompt('user-welcome')->compile([
        'name' => 'Alice',
        'role' => 'admin',
    ]);

    $dashboard = $langfuse->getPrompt('admin-dashboard')->compile([
        'name' => 'Alice',
    ]);

    expect($welcome)->toBe('Welcome Alice! Your role is admin.');
    expect($dashboard)->toBe('Admin dashboard for Alice');

    // Assert the flow
    $fake = LangfuseFake::getFake();
    $fake->assertPromptRequested('user-welcome')
        ->assertPromptRequested('admin-dashboard')
        ->assertPromptRequestedTimes('user-welcome', 1)
        ->assertPromptRequestedTimes('admin-dashboard', 1);
});

it('can test error scenarios', function () {
    LangfuseFake::fake()->throwOnMissing();

    $langfuse = app(Langfuse::class);

    expect(fn () => $langfuse->getPrompt('non-existent'))
        ->toThrow(Exception::class, "Prompt 'non-existent' not found in fake");
});

it('can test with trait integration', function () {
    // Using the trait automatically sets up the fake
    $fake = $this->getLangfuseFake();
    $fake->addPrompt('test', 'Test prompt');

    $langfuse = app(Langfuse::class);
    $result = $langfuse->getPrompt('test')->raw();

    expect($result)->toBe('Test prompt');
    $fake->assertPromptRequested('test');
});

it('can restore real instance', function () {
    LangfuseFake::fake(['test' => 'fake prompt']);

    // Verify fake is active
    $langfuse = app(Langfuse::class);
    expect($langfuse->isFake())->toBeTrue();

    // Restore real instance
    LangfuseFake::restore();

    $langfuse = app(Langfuse::class);
    expect($langfuse->isFake())->toBeFalse();
});
