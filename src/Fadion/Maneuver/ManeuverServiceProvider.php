<?php namespace Fadion\Maneuver;

use Illuminate\Support\ServiceProvider;
use Fadion\Maneuver\Commands\DeployCommand;
use Fadion\Maneuver\Commands\ListCommand;
use Fadion\Maneuver\Commands\RollbackCommand;
use Fadion\Maneuver\Commands\SyncCommand;

class ManeuverServiceProvider extends ServiceProvider
{

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/config.php' => config_path('maneuver.php')
        ]);
    }

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->commands([
			Commands\DeployCommand::class,
			Commands\ListCommand::class,
			Commands\RollbackCommand::class,
			Commands\SyncCommand::class,
		]);
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('maneuver');
	}

}
