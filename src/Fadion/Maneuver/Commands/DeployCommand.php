<?php namespace Fadion\Maneuver\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Fadion\Maneuver\Maneuver;
use Exception;

class DeployCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'deploy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start deployment maneuver.';

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $options = [
                'server' => $this->option('server'),
                'repo' => $this->option('repo'),
                'withForcedFiles' => $this->option('with-forced-files'),
            ];

            $maneuver = new Maneuver($options);
            $maneuver->mode(Maneuver::MODE_DEPLOY);
            $maneuver->start();
        } catch (Exception $e) {
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
        return [];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['server', 's', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Server to deploy to.', null],
            ['repo', 'r', InputOption::VALUE_OPTIONAL, 'Repository to use.', null],
            ['with-forced-files', 'wff', InputOption::VALUE_OPTIONAL, 'Add forced files/folders list to upload list.', null],
        ];
    }

}
