<?php

namespace dayemsiddiqui\Langfuse;

class Langfuse
{
    public function getPrompt(string $promptName, array $variables = []): string
    {
        dd($promptName, $variables);
    }
}
