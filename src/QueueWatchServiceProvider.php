<?php

namespace QueueWatch\QueueWatch;

use QueueWatch\QueueWatch\Commands\QueueWatchCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
