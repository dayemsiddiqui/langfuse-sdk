# Laravel Langfuse SDK

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dayemsiddiqui/langfuse-sdk.svg?style=flat-square)](https://packagist.org/packages/dayemsiddiqui/langfuse-sdk)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/dayemsiddiqui/langfuse-sdk/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/dayemsiddiqui/langfuse-sdk/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/dayemsiddiqui/langfuse-sdk.svg?style=flat-square)](https://packagist.org/packages/dayemsiddiqui/langfuse-sdk)

A simple Laravel package for working with [Langfuse](https://langfuse.com) prompts. Fetch, compile, and test AI prompts with ease.

---

## 🚀 Quick Start

1. **Install:**

```bash
composer require dayemsiddiqui/langfuse-sdk
```

2. **Add your API keys to `.env**:

```env
LANGFUSE_PUBLIC_KEY=your_public_key_here
LANGFUSE_SECRET_KEY=your_secret_key_here
LANGFUSE_HOST=https://cloud.langfuse.com
```

3. **Use in your code:**

```php
use dayemsiddiqui\Langfuse\Langfuse;

$langfuse = new Langfuse();

// Get a raw prompt
echo $langfuse->getPrompt('welcome')->raw();


// Compile a prompt with variables
echo $langfuse->getPrompt('greeting')->compile(['name' => 'Alice']);
```

---

## ✨ Features

-   **Fetch prompts** from Langfuse by name
-   **Compile prompts** with variables (e.g. `{{name}}`)
-   **Detailed error messages** for missing variables
-   **Elegant testing** with `LangfuseFake::fake()` (see below)

---

## 🧑‍💻 Usage

### Get a Prompt

```php
$prompt = $langfuse->getPrompt('welcome')->raw();
// "Hello {{name}}"
```

### Compile a Prompt

```php
$compiled = $langfuse->getPrompt('greeting')->compile(['name' => 'Alice']);
// "Hello Alice!"
```

### Handle Missing Variables

If you try to compile a prompt and dont provide all the required variables in a prompt you will get a MissingPromptVariablesException

```php
try {
    $langfuse->getPrompt('profile')->compile(['name' => 'John']);
} catch (\dayemsiddiqui\Langfuse\Exceptions\MissingPromptVariablesException $e) {
    echo $e->getMessage();
}
```

---

## 🧪 Testing (Just like Queue::fake())

Testing your code is easy and elegant!

### 1. Fake Langfuse in your tests

```php
use dayemsiddiqui\Langfuse\Testing\LangfuseFake;
use dayemsiddiqui\Langfuse\Langfuse;

LangfuseFake::fake([
    'greeting' => 'Hello {{name}}!',
    'farewell' => 'Goodbye {{name}}!'
]);

$langfuse = app(Langfuse::class);

// This will use the fake prompt, no HTTP requests!
echo $langfuse->getPrompt('greeting')->compile(['name' => 'Test']); // "Hello Test!"
```

### 2. Make assertions

```php
LangfuseFake::getFake()->assertPromptRequested('greeting');
LangfuseFake::getFake()->assertPromptRequestedTimes('greeting', 1);
LangfuseFake::getFake()->assertNoPromptsRequested();
```

### 3. Chainable API

```php
LangfuseFake::fake()
    ->addPrompt('foo', 'Foo {{bar}}')
    ->addPrompt('baz', 'Baz {{qux}}')
    ->throwOnMissing();
```

### 4. Use the trait for auto-setup

```php
use dayemsiddiqui\Langfuse\Testing\WithLangfuseFake;

uses(WithLangfuseFake::class);

it('fakes prompts', function () {
    $fake = $this->getLangfuseFake();
    $fake->addPrompt('test', 'Test prompt');
    $langfuse = app(Langfuse::class);
    expect($langfuse->getPrompt('test')->raw())->toBe('Test prompt');
});
```

---

## 📚 API Reference (Short)

-   `getPrompt($name)` → returns a PromptBuilder
-   `PromptBuilder->raw()` → get raw prompt
-   `PromptBuilder->compile($vars)` → compile with variables
-   `LangfuseFake::fake([...])` → fake prompts in tests
-   `LangfuseFake::getFake()->assertPromptRequested($name)` → assert prompt was used

---

## 📝 Advanced

-   **Dependency Injection:** Works with Laravel's container, so you can type-hint `Langfuse` in your services.
-   **Custom Exception:** Get missing variables, provided variables, prompt name/content from the exception.
-   **Supports both `{{name}}` and `{{ name }}` syntax.**

---

## 🛠️ Development

-   Run tests: `composer run test`
-   Format code: `composer format`
-   Static analysis: `composer analyse`

---

## 🤝 Contributing & License

-   PRs welcome! See [CONTRIBUTING](CONTRIBUTING.md).
-   MIT License. See [LICENSE.md](LICENSE.md).

---

## 🙏 Credits

-   [Dayem Siddiqui](https://github.com/dayemsiddiqui)
-   [All Contributors](../../contributors)
