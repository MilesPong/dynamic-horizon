<?php

namespace MilesPong\Horizon\Listeners;

use MilesPong\Horizon\DynamicHorizonSupervisor;
use Illuminate\Contracts\Foundation\Application;
use Laravel\Horizon\Events\MasterSupervisorLooped;

/**
 * Class DeployNewSupervisors
 *
 * @package MilesPong\Horizon\Listeners
 */
class DeployNewSupervisors
{
    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * DeployNewSupervisors constructor.
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
        $this->app->make(DynamicHorizonSupervisor::class)->deploy($event->master);
    }
}
