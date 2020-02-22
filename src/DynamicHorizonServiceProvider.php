<?php

namespace MilesPong\Horizon;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

/**
 * Class DynamicHorizonServiceProvider
 *
 * @package MilesPong\Horizon
 */
class DynamicHorizonServiceProvider extends ServiceProvider
{
    use EventMap;

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->configure();
        $this->offerPublishing();

        $this->app->singleton(DynamicHorizonSupervisor::class, function (Application $app) {
            return new DynamicHorizonSupervisor($app, new Repository(
                $app['redis'],
                $app['config']['dynamic-horizon.plans_prefix'],
                $app['config']['dynamic-horizon.redis_connection']
            ), $app['config']['dynamic-horizon.horizon_master_id']
            );
        });
    }

    /**
     *
     */
    protected function configure()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/dynamic-horizon.php', 'dynamic-horizon');
    }

    /**
     *
     */
    protected function offerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/dynamic-horizon.php' => $this->app->configPath('dynamic-horizon.php')
            ], 'config');
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot()
    {
        $this->registerEvents();
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function registerEvents()
    {
        $eventDispatcher = $this->app->make(Dispatcher::class);

        foreach ($this->events as $event => $listeners) {
            foreach ((array)$listeners as $listener) {
                $eventDispatcher->listen($event, $listener);
            }
        }
    }
}
