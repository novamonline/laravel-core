<?php

namespace Core\Boot\Providers;

use Core\Dev\Commands\MakeTraitCommand;
use Illuminate\Support\ServiceProvider;
use Core\Dev\Commands\RunInstallCommand;
use Core\Dev\Commands\MakeRepoCommand;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * The available commands
     *
     * @var array
     */
    protected $commands = [
        RunInstallCommand::class,
        MakeTraitCommand::class,
        MakeRepoCommand::class,
    ];

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {       

        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
        $this->publishes([
            __DIR__.'/../../../lib/stubs' => base_path('stubs/custom'),
        ], 'stubs');

        $this->publishes([
            __DIR__.'/../../Conf/core.php' => config_path('core.php'),
        ], 'config');
    }
}
