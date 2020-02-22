<?php

return [
    'horizon_master_id' => env('HORIZON_MASTER_ID'),
    'plans_prefix' => env('DYNAMIC_HORIZON_PLANS_PREFIX', 'dynamic_horizon_plans'),
    'redis_connection' => env('DYNAMIC_HORIZON_CONNECTION', 'default')
];
