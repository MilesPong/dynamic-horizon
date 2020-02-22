<?php

namespace MilesPong\Horizon\Events;

use Laravel\Horizon\MasterSupervisor;

/**
 * Class DynamicHorizonInitialized
 *
 * @package MilesPong\Horizon\Events
 */
class DynamicHorizonInitialized
{
    /**
     * The master supervisor instance.
     *
     * @var string|null
     */
    public $masterId;

    /**
     * @var \Laravel\Horizon\MasterSupervisor
     */
    public $master;

    /**
     * Create a new event instance.
     *
     * @param string|null $masterId
     * @param \Laravel\Horizon\MasterSupervisor $master
     */
    public function __construct(?string $masterId, MasterSupervisor $master)
    {
        $this->masterId = $masterId;
        $this->master = $master;
    }
}
