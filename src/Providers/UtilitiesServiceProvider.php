<?php namespace Arcanedev\LogViewer\Providers;

use Arcanedev\LogViewer\Contracts;
use Arcanedev\LogViewer\Utilities;
use Arcanedev\Support\ServiceProvider;

/**
 * Class     UtilitiesServiceProvider
 *
 * @package  Arcanedev\LogViewer\Providers
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class UtilitiesServiceProvider extends ServiceProvider
{
    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerLogLevels();
        $this->registerStyler();
        $this->registerLogMenu();
        $this->registerFilesystem();
        $this->registerFactory();
        $this->registerChecker();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'arcanedev.log-viewer.levels',
            Contracts\Utilities\LogLevels::class,
            'arcanedev.log-viewer.styler',
            Contracts\Utilities\LogStyler::class,
            'arcanedev.log-viewer.menu',
            Contracts\Utilities\LogMenu::class,
            'arcanedev.log-viewer.filesystem',
            Contracts\Utilities\Filesystem::class,
            'arcanedev.log-viewer.factory',
            Contracts\Utilities\Factory::class,
            'arcanedev.log-viewer.checker',
            Contracts\Utilities\LogChecker::class,
        ];
    }

    /* ------------------------------------------------------------------------------------------------
     |  The LogViewer Utilities
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Register the log levels.
     */
    private function registerLogLevels()
    {
        $this->singleton(Contracts\Utilities\LogLevels::class, function ($app) {
            /**
             * @var  \Illuminate\Config\Repository       $config
             * @var  \Illuminate\Translation\Translator  $translator
             */
            $config     = $app['config'];
            $translator = $app['translator'];

            return new Utilities\LogLevels($translator, $config->get('log-viewer.locale'));
        });

        $this->singleton('arcanedev.log-viewer.levels', Contracts\Utilities\LogLevels::class);
    }

    /**
     * Register the log styler.
     */
    private function registerStyler()
    {
        $this->singleton(Contracts\Utilities\LogStyler::class, Utilities\LogStyler::class);
        $this->singleton('arcanedev.log-viewer.styler', Contracts\Utilities\LogStyler::class);
    }

    /**
     * Register the log menu builder.
     */
    private function registerLogMenu()
    {
        $this->singleton(Contracts\Utilities\LogMenu::class, Utilities\LogMenu::class);
        $this->singleton('arcanedev.log-viewer.menu', Contracts\Utilities\LogMenu::class);
    }

    /**
     * Register the log filesystem.
     */
    private function registerFilesystem()
    {
        $this->singleton(Contracts\Utilities\Filesystem::class, function ($app) {
            /**
             * @var  \Illuminate\Config\Repository      $config
             * @var  \Illuminate\Filesystem\Filesystem  $files
             */
            $config     = $app['config'];
            $files      = $app['files'];
            $filesystem = new Utilities\Filesystem($files, $config->get('log-viewer.storage-path'));

            $filesystem->setPattern(
                $config->get('log-viewer.pattern.prefix',    Utilities\Filesystem::PATTERN_PREFIX),
                $config->get('log-viewer.pattern.date',      Utilities\Filesystem::PATTERN_DATE),
                $config->get('log-viewer.pattern.extension', Utilities\Filesystem::PATTERN_EXTENSION)
            );

            return $filesystem;
        });

        $this->singleton('arcanedev.log-viewer.filesystem', Contracts\Utilities\Filesystem::class);
    }

    /**
     * Register the log factory class.
     */
    private function registerFactory()
    {
        $this->singleton(Contracts\Utilities\Factory::class, Utilities\Factory::class);
        $this->singleton('arcanedev.log-viewer.factory', Contracts\Utilities\Factory::class);
    }

    /**
     * Register the log checker service.
     */
    private function registerChecker()
    {
        $this->singleton(Contracts\Utilities\LogChecker::class, Utilities\LogChecker::class);
        $this->singleton('arcanedev.log-viewer.checker', Contracts\Utilities\LogChecker::class);
    }
}
