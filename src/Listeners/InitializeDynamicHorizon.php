<?php

namespace MilesPong\Horizon\Listeners;

use MilesPong\Horizon\DynamicHorizonSupervisor;
use MilesPong\Horizon\Events\DynamicHorizonInitialized;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Laravel\Horizon\Events\MasterSupervisorLooped;

/**
 * Class InitializeDynamicHorizon
 *
 * @package MilesPong\Horizon\Listeners
 */
class InitializeDynamicHorizon
{
    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * InitializeDynamicHorizon constructor.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param \Laravel\Horizon\Events\MasterSupervisorLooped $event
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function handle(MasterSupervisorLooped $event)
    {
        $dynamicHorizonSupervisor = $this->app->make(DynamicHorizonSupervisor::class);

        // Determine if it is the first boot
        if ($dynamicHorizonSupervisor->isBooted()) {
            return;
        }

        // Initialization
        // Get current master identifier's supervisors and inject them
        // Call deploy method
        $dynamicHorizonSupervisor->deployInitialPlans($event->master);

        // Mark as initialized
        $this->app->make(Dispatcher::class)->dispatch(new DynamicHorizonInitialized($dynamicHorizonSupervisor->getMasterIdentifier(),
            $event->master));
    }
}
