<?php

namespace QueueWatch\QueueWatch;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use QueueWatch\QueueWatch\Commands\QueueWatchCommand;

class QueueWatchServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('queue-watch')
            ->hasConfigFile()
            ->hasCommand(QueueWatchCommand::class);
    }
}
