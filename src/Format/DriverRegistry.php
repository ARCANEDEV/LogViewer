<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Format;

use Arcanedev\LogViewer\Contracts\FormatDriver;
use Closure;

/**
 * Class     DriverRegistry
 *
 * Resolves the {@see FormatDriver} for a log format and lets host applications
 * register their own via {@see extend()}.
 *
 * Resolution order (for a given name, defaulting to `config('log-viewer.formatter')`):
 *   1. A driver registered by the host app under that name.
 *   2. Legacy behavior — preserved exactly: `default` => DefaultDriver,
 *      any other value => JsonLinesDriver.
 */
class DriverRegistry
{
    /**
     * Custom driver factories keyed by name.
     *
     * Each factory is a `\Closure` returning a {@see FormatDriver}, keyed by name.
     *
     * @var array
     */
    protected $factories = [];

    /**
     * Register (or override) a driver factory.
     *
     * @return $this
     */
    public function extend(string $name, Closure $factory): self
    {
        $this->factories[$name] = $factory;

        return $this;
    }

    /**
     * Whether a custom driver is registered under the given name.
     */
    public function has(string $name): bool
    {
        return isset($this->factories[$name]);
    }

    /**
     * Resolve a format driver.
     *
     * @param  string|null  $name  Defaults to the configured formatter.
     */
    public function driver(?string $name = null): FormatDriver
    {
        $name = $name ?: config('log-viewer.formatter', 'default');

        if (isset($this->factories[$name])) {
            return ($this->factories[$name])();
        }

        // Legacy fallback — same decision the old load() made.
        return $name === 'default'
            ? new DefaultDriver
            : new JsonLinesDriver;
    }
}
