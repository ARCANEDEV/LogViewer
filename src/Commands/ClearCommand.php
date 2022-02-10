<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Commands;

/**
 * Class     ClearCommand
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class ClearCommand extends Command
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log-viewer:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all generated log files';

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->confirm('This will delete all the log files, Do you wish to continue?')) {
            $this->logViewer->clear();
            $this->info('Successfully cleared the logs!');
        }

        return static::SUCCESS;
    }
}
