<?php

namespace MilesPong\Horizon\Listeners;

use MilesPong\Horizon\DynamicHorizonSupervisor;
use MilesPong\Horizon\Events\DynamicHorizonInitialized;
use Illuminate\Contracts\Foundation\Application;

/**
 * Class MarkManagerAsBooted
 *
 * @package MilesPong\Horizon\Listeners
 */
class MarkManagerAsBooted
{
    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * MarkManagerAsBooted constructor.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param \MilesPong\Horizon\Events\DynamicHorizonInitialized $event
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function handle(DynamicHorizonInitialized $event)
    {
        $this->app->make(DynamicHorizonSupervisor::class)->setBoot(true);
    }
}
