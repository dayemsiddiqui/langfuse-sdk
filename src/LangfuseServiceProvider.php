<?php

namespace dayemsiddiqui\Langfuse;

use dayemsiddiqui\Langfuse\Commands\LangfuseCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LangfuseServiceProvider extends PackageServiceProvider
{
    public function register(): void
    {
        parent::register();

        $this->mergeConfigFrom(__DIR__.'/../config/langfuse-sdk.php', 'langfuse-sdk');

        $this->app->singleton(Langfuse::class, function ($app) {
            return new Langfuse;
        });

        // Register the fake binding
        $this->app->singleton('langfuse.fake', function ($app) {
            return new \dayemsiddiqui\Langfuse\Testing\FakeLangfuse;
        });
    }

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
