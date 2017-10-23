<?php namespace Fadion\Maneuver\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Fadion\Maneuver\Maneuver;
use Exception;

class SyncCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'deploy:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync remote revision file with the local one.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Calls the fire method
     *
     * @return void
     */
    public function handle() {
      $this->fire();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        try {
            $options = array(
                'server' => $this->option('server'),
                'repo' => $this->option('repo'),
                'sync' => $this->option('commit')
            );

            $maneuver = new Maneuver($options);
            $maneuver->mode(Maneuver::MODE_SYNC);
            $maneuver->start();
        }
        catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('server', 's', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Server to deploy to.', null),
            array('repo', 'r', InputOption::VALUE_OPTIONAL, 'Repository to use.', null),
            array('commit', 'c', InputOption::VALUE_OPTIONAL, 'Commit to sync to.', null)
        );
    }

}
