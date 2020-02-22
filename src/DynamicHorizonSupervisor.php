<?php

namespace MilesPong\Horizon;

use MilesPong\Horizon\Events\DynamicSupervisorsDeployed;
use MilesPong\Horizon\Exceptions\InvalidSupervisorPlans;
use Closure;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Laravel\Horizon\MasterSupervisor;
use Laravel\Horizon\ProvisioningPlan;

/**
 * Class DynamicHorizonSupervisor
 *
 * @package MilesPong\Horizon
 */
class DynamicHorizonSupervisor
{
    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * @var \MilesPong\Horizon\Repository
     */
    protected $repository;

    /**
     * @var array
     */
    protected $initialClosurePlans = [];

    /**
     * @var
     */
    private $masterId;

    /**
     * @var bool
     */
    private $boot = false;

    /**
     * @var string
     */
    private $dynamicHorizonEnv = 'dynamic_horizon_env';

    /**
     * DynamicHorizonSupervisor constructor.
     *
     * @param \Illuminate\Contracts\Foundation\Application $application
     * @param \MilesPong\Horizon\Repository $repository
     * @param string $masterId
     */
    public function __construct(Application $application, Repository $repository, ?string $masterId)
    {
        $this->app = $application;

        $this->repository = $repository;

        $this->masterId = $masterId;
    }

    /**
     * @param bool $isBooted
     * @return $this
     */
    public function setBoot(bool $isBooted)
    {
        $this->boot = $isBooted;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBooted()
    {
        return boolval($this->boot);
    }

    /**
     * @return mixed
     */
    public function getMasterIdentifier()
    {
        return $this->masterId;
    }

    /**
     * Add initial plans
     *
     * Usually called in AppServiceProvider
     *
     * @param string $masterId
     * @param \Closure $callback
     * @see \MilesPong\Horizon\DynamicHorizonSupervisor::deployInitialPlans
     */
    public function initial(string $masterId, Closure $callback)
    {
        // Only execute in console mode
        if (!$this->app->runningInConsole()) {
            return;
        }

        // Determine if current master is the requested master
        if ($this->masterId !== $masterId) {
            return;
        }

        // Only handle logic for current master
        // Add it to memory
        $this->addInitialClosurePlans($masterId, $callback);
    }

    /**
     * @param string $masterId
     * @param \Closure $callback
     */
    protected function addInitialClosurePlans(string $masterId, Closure $callback)
    {
        if (!isset($this->initialClosurePlans[$masterId])) {
            $this->initialClosurePlans[$masterId] = [];
        }

        array_push($this->initialClosurePlans[$masterId], $callback);
    }

    /**
     * Deploy plans after first loop
     *
     * @param \Laravel\Horizon\MasterSupervisor $masterSupervisor
     * @throws \MilesPong\Horizon\Exceptions\InvalidSupervisorPlans
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @see \MilesPong\Horizon\DynamicHorizonSupervisor::initial
     */
    public function deployInitialPlans(MasterSupervisor $masterSupervisor)
    {
        // Skip while not set master id (Only safe check)
        if (!$this->masterId) {
            return;
        }

        foreach ((array)$this->getInitialClosurePlans($this->masterId) as $closurePlans) {
            // Get closure plans for current master from memory
            $plans = $this->getPlansInSafe($closurePlans);

            // Parse closure and set config
            $this->setDynamicHorizonConfig($plans);

            // Call master method to deploy plans
            $this->deploySupervisors();
        }

        // Unset config
        $this->setDynamicHorizonConfig(null);
    }

    /**
     * @param string $masterId
     * @return array|mixed
     */
    protected function getInitialClosurePlans(string $masterId)
    {
        return $this->initialClosurePlans[$masterId] ?? [];
    }

    /**
     * @param \Closure $callback
     * @return mixed
     * @throws \MilesPong\Horizon\Exceptions\InvalidSupervisorPlans
     */
    protected function getPlansInSafe(Closure $callback)
    {
        $plans = $callback();

        $this->validateClosurePlans($plans);

        return $plans;
    }

    /**
     * @param array $plans
     * @throws \MilesPong\Horizon\Exceptions\InvalidSupervisorPlans
     */
    protected function validateClosurePlans(array $plans)
    {
        // TODO enhancement validation rules
        if (!is_array($plans)) {
            throw new InvalidSupervisorPlans('Invalid supervisors plans');
        }
    }

    /**
     * @param array|null $plans
     * @return mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function setDynamicHorizonConfig(?array $plans = null)
    {
        return $this->app->make('config')->set("horizon.environments.{$this->dynamicHorizonEnv}", $plans);
    }

    /**
     *
     */
    protected function deploySupervisors()
    {
        ProvisioningPlan::get(MasterSupervisor::name())->deploy($this->dynamicHorizonEnv);
    }

    /**
     * Dynamic adding supervisors after initialization
     *
     * @param $masterId
     * @param \Closure $callback
     * @return mixed
     * @throws \MilesPong\Horizon\Exceptions\InvalidSupervisorPlans
     * @see \MilesPong\Horizon\DynamicHorizonSupervisor::deploy
     */
    public function add($masterId, Closure $callback)
    {
        // Parse closure and validate
        $plans = $this->getPlansInSafe($callback);

        // Push payload to store (redis:list, key: master_id, value: plans)
        return $this->repository->push($masterId, $plans);
    }

    /**
     * Dynamic deploy new supervisors (none initialization scene)
     *
     * Usually called in after every master supervisor loop
     *
     * @param \Laravel\Horizon\MasterSupervisor $masterSupervisor
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @see \MilesPong\Horizon\DynamicHorizonSupervisor::add
     */
    public function deploy(MasterSupervisor $masterSupervisor)
    {
        // Skip while not set master id
        if (!$this->masterId) {
            return;
        }

        // Iterate list for current master id (redis:list, pop)
        while ($plans = $this->repository->pop($this->masterId)) {
            // Set config
            $this->setDynamicHorizonConfig($plans);

            // Call master method to deploy plans
            $this->deploySupervisors();

            $this->app->make(Dispatcher::class)->dispatch(new DynamicSupervisorsDeployed($plans, $this->masterId,
                $masterSupervisor));
        }

        // Unset config
        $this->setDynamicHorizonConfig(null);
    }
}
