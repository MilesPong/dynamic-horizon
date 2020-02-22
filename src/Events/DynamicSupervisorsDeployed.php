<?php

namespace MilesPong\Horizon\Events;

use Laravel\Horizon\MasterSupervisor;

/**
 * Class DynamicSupervisorsDeployed
 *
 * @package MilesPong\Horizon\Events
 */
class DynamicSupervisorsDeployed
{
    /**
     * The master supervisor instance.
     *
     * @var string
     */
    public $masterId;

    /**
     * @var \Laravel\Horizon\MasterSupervisor
     */
    public $master;

    /**
     * @var array
     */
    public $plans;

    /**
     * Create a new event instance.
     *
     * @param array $plans
     * @param string $masterId
     * @param \Laravel\Horizon\MasterSupervisor $master
     */
    public function __construct(array $plans, string $masterId, MasterSupervisor $master)
    {
        $this->plans = $plans;
        $this->masterId = $masterId;
        $this->master = $master;
    }
}
