<?php namespace Arcanedev\LogViewer\Commands;

/**
 * Class     PublishCommand
 *
 * @package  Arcanedev\LogViewer\Commands
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class CheckCommand extends Command
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name      = 'log-viewer:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all LogViewer requirements.';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log-viewer:check';

    /** @var \Arcanedev\LogViewer\Contracts\Utilities\LogChecker */
    private $checker;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->displayLogViewer();

        $this->checker = $this->laravel['arcanedev.log-viewer.checker'];

        $this->displayRequirements();
        $this->displayMessages();
    }

    /**
     * Display LogViewer requirements.
     */
    private function displayRequirements()
    {
        $requirements = $this->checker->requirements();

        $this->frame('Application requirements');

        $this->table([
            'Status', 'Message'
        ], [
            [$requirements['status'], $requirements['message']]
        ]);
    }

    /**
     * Display LogViewer messages.
     */
    private function displayMessages()
    {
        $messages = $this->checker->messages();

        $rows = [];
        foreach ($messages['files'] as $file => $message) {
            $rows[] = [$file, $message];
        }

        $this->frame('LogViewer messages');
        $this->table(['File', 'Message'], $rows);
    }
}
