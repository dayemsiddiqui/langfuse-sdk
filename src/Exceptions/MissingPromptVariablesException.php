<?php

namespace dayemsiddiqui\Langfuse\Exceptions;

use Exception;

class MissingPromptVariablesException extends Exception
{
    protected array $missingVariables;

    protected array $providedVariables;

    protected string $promptName;

    protected string $promptContent;

    public function __construct(
        array $missingVariables,
        array $providedVariables,
        string $promptName,
        string $promptContent,
        string $message = '',
        int $code = 0,
        ?Exception $previous = null
    ) {
        $this->missingVariables = $missingVariables;
        $this->providedVariables = $providedVariables;
        $this->promptName = $promptName;
        $this->promptContent = $promptContent;

        if (empty($message)) {
            $message = $this->generateMessage();
        }

        parent::__construct($message, $code, $previous);
    }

    protected function generateMessage(): string
    {
        $missingCount = count($this->missingVariables);
        $missingList = implode(', ', $this->missingVariables);
        $providedList = empty($this->providedVariables) ? 'none' : implode(', ', array_keys($this->providedVariables));

        $message = "Missing required variables for prompt '{$this->promptName}': {$missingList}";
        $message .= "\n\n";
        $message .= "Variables provided: {$providedList}";
        $message .= "\n\n";
        $message .= 'Solution: Please provide values for the missing '.($missingCount === 1 ? 'variable' : 'variables').' when calling getPrompt():';
        $message .= "\n";
        $message .= "\$langfuse->getPrompt('{$this->promptName}', [";

        foreach ($this->providedVariables as $key => $value) {
            $message .= "\n    '{$key}' => '{$value}',";
        }

        foreach ($this->missingVariables as $variable) {
            $message .= "\n    '{$variable}' => 'your_value_here',";
        }

        $message .= "\n]);";

        return $message;
    }

    public function getMissingVariables(): array
    {
        return $this->missingVariables;
    }

    public function getProvidedVariables(): array
    {
        return $this->providedVariables;
    }

    public function getPromptName(): string
    {
        return $this->promptName;
    }

    public function getPromptContent(): string
    {
        return $this->promptContent;
    }
}
