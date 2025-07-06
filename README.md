# Laravel Langfuse SDK

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dayemsiddiqui/langfuse-sdk.svg?style=flat-square)](https://packagist.org/packages/dayemsiddiqui/langfuse-sdk)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/dayemsiddiqui/langfuse-sdk/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/dayemsiddiqui/langfuse-sdk/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/dayemsiddiqui/langfuse-sdk/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/dayemsiddiqui/langfuse-sdk/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/dayemsiddiqui/langfuse-sdk.svg?style=flat-square)](https://packagist.org/packages/dayemsiddiqui/langfuse-sdk)

A Laravel package for interacting with the [Langfuse](https://langfuse.com) API. This SDK provides a simple and elegant way to fetch and process AI prompts from your Langfuse account, with built-in variable replacement and comprehensive error handling.

## Features

- 🚀 **Easy Integration**: Simple Laravel service provider integration
- 🔧 **Variable Processing**: Automatic variable replacement in prompts with validation
- 🎯 **Smart Validation**: Comprehensive validation with detailed error messages
- 🛡️ **Exception Handling**: Custom exceptions with actionable error messages
- 📝 **Template Support**: Support for `{{variable}}` and `{{ variable }}` syntax
- 🔐 **Secure**: Built-in authentication with Langfuse API

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/langfuse-sdk.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/langfuse-sdk)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require dayemsiddiqui/langfuse-sdk
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="langfuse-sdk-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="langfuse-sdk-config"
```

This is the contents of the published config file:

```php
return [
    'public_key' => env('LANGFUSE_PUBLIC_KEY'),
    'secret_key' => env('LANGFUSE_SECRET_KEY'),
    'host' => env('LANGFUSE_HOST', 'https://cloud.langfuse.com'),
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="langfuse-sdk-views"
```

## Configuration

Add your Langfuse credentials to your `.env` file:

```env
LANGFUSE_PUBLIC_KEY=your_public_key_here
LANGFUSE_SECRET_KEY=your_secret_key_here
LANGFUSE_HOST=https://cloud.langfuse.com
```

### Getting Your Langfuse API Keys

1. Sign up for a [Langfuse account](https://langfuse.com)
2. Go to your project settings
3. Navigate to the "API Keys" section
4. Copy your Public Key and Secret Key
5. Add them to your `.env` file as shown above

## Usage

### Basic Usage

```php
use dayemsiddiqui\Langfuse\Langfuse;

$langfuse = new Langfuse();

// Get a simple prompt without variables
$prompt = $langfuse->getPrompt('welcome-message');
echo $prompt; // "Welcome to our application!"
```

### Using Variables in Prompts

```php
use dayemsiddiqui\Langfuse\Langfuse;

$langfuse = new Langfuse();

// Get a prompt with variables
$prompt = $langfuse->getPrompt('user-greeting', [
    'name' => 'John Doe',
    'company' => 'Acme Corp'
]);

// If your Langfuse prompt is: "Hello {{name}}! Welcome to {{company}}."
// Result: "Hello John Doe! Welcome to Acme Corp."
echo $prompt;
```

### Variable Syntax Support

The SDK supports both compact and spaced variable syntax:

```php
// Both of these work:
// {{name}} - compact syntax
// {{ name }} - spaced syntax

$prompt = $langfuse->getPrompt('flexible-template', [
    'user' => 'Alice',
    'action' => 'login'
]);
```

## Error Handling

The SDK provides comprehensive error handling with detailed, actionable error messages:

### Missing Variables Exception

```php
use dayemsiddiqui\Langfuse\Langfuse;
use dayemsiddiqui\Langfuse\Exceptions\MissingPromptVariablesException;

try {
    $langfuse = new Langfuse();

    // This will throw an exception if variables are missing
    $prompt = $langfuse->getPrompt('user-profile', [
        'name' => 'John'
        // Missing 'email' and 'phone' variables
    ]);
} catch (MissingPromptVariablesException $e) {
    // Get detailed error information
    echo $e->getMessage();

    // Get specific missing variables
    $missingVars = $e->getMissingVariables(); // ['email', 'phone']

    // Get provided variables
    $providedVars = $e->getProvidedVariables(); // ['name' => 'John']

    // Get the prompt name and content
    $promptName = $e->getPromptName(); // 'user-profile'
    $promptContent = $e->getPromptContent(); // The original prompt template
}
```

### Example Error Message

When variables are missing, you'll get a detailed error message like:

```
❌ Missing required variables in prompt 'user-profile'

Missing variables: email, phone
Provided variables: name

📋 Prompt content:
Hello {{name}}! Your email is {{email}} and phone is {{phone}}.

💡 To fix this error:
1. Add the missing variables to your getPrompt() call:
   $langfuse->getPrompt('user-profile', [
       'name' => 'value',
       'email' => 'value',
       'phone' => 'value'
   ]);

2. Or update your prompt template to remove unused variables.
```

## API Reference

### `Langfuse` Class

#### `getPrompt(string $promptName, array $variables = []): string`

Fetches a prompt from Langfuse and processes variables.

**Parameters:**

- `$promptName` (string): The name of the prompt in Langfuse
- `$variables` (array, optional): Associative array of variables to replace in the prompt

**Returns:** `string` - The processed prompt with variables replaced

**Throws:**

- `MissingPromptVariablesException` - When required variables are missing
- `Exception` - When API request fails

#### `getPublicKey(): string`

Returns the configured public key.

#### `getSecretKey(): string`

Returns the configured secret key.

#### `getHost(): string`

Returns the configured Langfuse host URL.

### `MissingPromptVariablesException` Class

#### `getMissingVariables(): array`

Returns an array of variable names that are missing.

#### `getProvidedVariables(): array`

Returns an associative array of variables that were provided.

#### `getPromptName(): string`

Returns the name of the prompt that caused the exception.

#### `getPromptContent(): string`

Returns the original prompt template content.

## Best Practices

### 1. Always Handle Exceptions

```php
use dayemsiddiqui\Langfuse\Exceptions\MissingPromptVariablesException;

try {
    $prompt = $langfuse->getPrompt('template-name', $variables);
} catch (MissingPromptVariablesException $e) {
    // Log the error or show user-friendly message
    logger()->error('Prompt template error: ' . $e->getMessage());
    return 'Error loading template';
}
```

### 2. Validate Variables Before Calling

```php
$requiredVars = ['name', 'email', 'company'];
$providedVars = ['name' => 'John', 'email' => 'john@example.com'];

$missingVars = array_diff($requiredVars, array_keys($providedVars));
if (!empty($missingVars)) {
    throw new InvalidArgumentException('Missing variables: ' . implode(', ', $missingVars));
}
```

### 3. Use Dependency Injection

```php
class EmailService
{
    public function __construct(private Langfuse $langfuse)
    {
    }

    public function sendWelcomeEmail(User $user): void
    {
        $emailBody = $this->langfuse->getPrompt('welcome-email', [
            'name' => $user->name,
            'email' => $user->email,
        ]);

        // Send email...
    }
}
```

## Testing

```bash
composer test
```

## Development

### Running Tests

```bash
cd packages/dayemsiddiqui/langfuse-sdk
composer test
```

### Code Style

```bash
composer format
```

### Static Analysis

```bash
composer analyse
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Dayem Siddiqui](https://github.com/dayemsiddiqui)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
