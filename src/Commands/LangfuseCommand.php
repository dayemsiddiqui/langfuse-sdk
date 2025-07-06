<?php

namespace dayemsiddiqui\Langfuse\Commands;

use Illuminate\Console\Command;

class LangfuseCommand extends Command
{
    public $signature = 'langfuse-sdk';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
