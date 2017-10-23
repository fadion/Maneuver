<?php namespace Fadion\Maneuver\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Fadion\Maneuver\Maneuver;
use Exception;

class ListCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'deploy:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List changed files since the last deployment.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

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
                'repo' => $this->option('repo')
            );

            $maneuver = new Maneuver($options);
            $maneuver->mode(Maneuver::MODE_LIST);
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
        );
    }

}
