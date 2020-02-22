<?php

namespace MilesPong\Horizon;

use MilesPong\Horizon\Events\DynamicHorizonInitialized;
use MilesPong\Horizon\Events\DynamicSupervisorsDeployed;
use MilesPong\Horizon\Listeners\DeployNewSupervisors;
use MilesPong\Horizon\Listeners\InitializeDynamicHorizon;
use MilesPong\Horizon\Listeners\MarkManagerAsBooted;
use Laravel\Horizon\Events\MasterSupervisorLooped;

/**
 * Trait EventMap
 *
 * @package MilesPong\Horizon
 */
trait EventMap
{
    /**
     * @var array
     */
    protected $events = [
        MasterSupervisorLooped::class => [
            InitializeDynamicHorizon::class,
            DeployNewSupervisors::class,
        ],
        DynamicHorizonInitialized::class => [
            MarkManagerAsBooted::class,
        ],
        DynamicSupervisorsDeployed::class => [

        ]
    ];
}
