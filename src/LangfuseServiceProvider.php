<?php

namespace dayemsiddiqui\Langfuse;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use dayemsiddiqui\Langfuse\Commands\LangfuseCommand;

class LangfuseServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('langfuse-sdk')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_langfuse_sdk_table')
            ->hasCommand(LangfuseCommand::class);
    }
}
