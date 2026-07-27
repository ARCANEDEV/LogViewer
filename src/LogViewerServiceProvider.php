<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer;

use Arcanedev\Support\Providers\PackageServiceProvider;

/**
 * Class     LogViewerServiceProvider
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogViewerServiceProvider extends PackageServiceProvider
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /**
     * Package name.
     *
     * @var string
     */
    protected $package = 'log-viewer';

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        parent::register();

        $this->registerConfig();

        $this->registerFormatDrivers();

        $this->registerStreamingEngine();

        $this->booted(function () {
            $this->registerProvider(Providers\RouteServiceProvider::class);
        });

        $this->registerCommands([
            Commands\PublishCommand::class,
            Commands\StatsCommand::class,
            Commands\CheckCommand::class,
            Commands\ClearCommand::class,
            Commands\WarmIndexCommand::class,
        ]);
    }

    /**
     * Register the format driver registry.
     */
    private function registerFormatDrivers(): void
    {
        $this->app->singleton(Format\DriverRegistry::class, function () {
            return new Format\DriverRegistry;
        });
    }

    /**
     * Register the streaming index engine services.
     */
    private function registerStreamingEngine(): void
    {
        $this->app->singleton(Contracts\LogIndexer::class, Index\Scanner::class);

        $this->app->singleton(Contracts\IndexStore::class, function ($app) {
            return new Index\BinaryIndexStore(
                $app->make(Contracts\LogIndexer::class),
                config('log-viewer.streaming.index-path')
            );
        });

        $this->app->singleton(Index\DeltaScanner::class, function ($app) {
            return new Index\DeltaScanner(
                $app->make(Contracts\IndexStore::class),
                $app->make(Contracts\LogIndexer::class)
            );
        });

        $lockDir = config('log-viewer.streaming.index-path');

        $this->app->singleton(Concurrency\BuildLock::class, function () use ($lockDir) {
            return new Concurrency\BuildLock($lockDir);
        });

        $this->app->singleton(Concurrency\BuildSemaphore::class, function () use ($lockDir) {
            return new Concurrency\BuildSemaphore(
                (int) config('log-viewer.streaming.max-build-concurrency', 2),
                $lockDir
            );
        });

        $this->app->singleton(Index\IndexManager::class, function ($app) {
            return new Index\IndexManager(
                $app->make(Contracts\IndexStore::class),
                $app->make(Index\DeltaScanner::class),
                $app->make(Concurrency\BuildLock::class),
                $app->make(Concurrency\BuildSemaphore::class)
            );
        });

        $this->app->singleton(Reader\TailReader::class, function ($app) {
            return new Reader\TailReader(
                $app->make(Contracts\LogIndexer::class),
                (int) config('log-viewer.streaming.tail-bytes', 25 * 1024 * 1024)
            );
        });

        $this->app->singleton(Reader\PageReader::class, function ($app) {
            return new Reader\PageReader(
                $app->make(Contracts\IndexStore::class),
                $app->make(Index\IndexManager::class),
                $app->make(Reader\TailReader::class)
            );
        });

        $this->app->singleton(Reader\SearchReader::class, function ($app) {
            return new Reader\SearchReader(
                $app->make(Contracts\IndexStore::class),
                $app->make(Index\IndexManager::class),
                $app->make(Reader\TailReader::class)
            );
        });

        $this->app->singleton(Reader\ViewerLogFactory::class, function ($app) {
            return new Reader\ViewerLogFactory(
                $app->make(Format\DriverRegistry::class),
                $app->make(Reader\PageReader::class),
                $app->make(Reader\SearchReader::class),
                $app->make(Index\IndexManager::class)
            );
        });
    }

    /**
     * Boot the service provider.
     */
    public function boot(): void
    {
        $this->loadTranslations();
        $this->loadViews();

        if ($this->app->runningInConsole()) {
            $this->publishConfig();
            $this->publishTranslations();
            $this->publishViews();
        }
    }
}
