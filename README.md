# Dynamic Horizon  
  
This package can help to extend [Laravel Horizon](https://laravel.com/docs/5.8/horizon) supervisors dynamically.  
  
## Feature  
  
**Laravel Horizon**, which creating supervisors from the very beginning configurations, with a high constraint in `horizon.php`  
  
**Dynamic Horizon**, which is based on Laravel Horizon, can create the supervisors dynamically despite the initial config file. This can be very helpful in a SaaS system  
  
## Install  
  
Require this package with composer using the following command:  

```php  
composer require milespong/dynamic-horizon
```
  
After updating composer, add the service provider to the providers array in `config/app.php`  

```php  
MilesPong\Horizon\DynamicHorizonServiceProvider::class
```

Laravel 5.5+ uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.  
  
## Usage  
  
Publish the config file `dynamic-horizon.php`  
  
```php  
php artisan vendor:publish --provider="MilesPong\Horizon\DynamicHorizonServiceProvider" --tag=config
```

Set a value `HORIZON_MASTER_ID` in `.env` to indicate a identifier for current horizon master supervisor  

### Initialize Supervisors  
  
In `AppServiceProvider`, add below code fragment in `boot` method  
  
```php  
use \MilesPong\Horizon\DynamicHorizonSupervisor;  
  
...  
  
public function boot()  
{  
    $this->app->make(DynamicHorizonSupervisor::class)->initial('YOUR_MASTER_ID', function () {  
        return [  
            'supervisor-1' => [  
                'connection' => 'redis',  
                'queue' => ['foo'],  
                'balance' => 'auto',  
                'processes' => 10,  
                'tries' => 3,  
                'sleep' => 3,  
            ],  
            // 'supervisor-2', 'supervisor-3', ...  
        ];  
    });  
}  
```
  
### Deploy New Supervisors  
  
When you want add the supervisors in other procedure rather than the bootstrap, use the way below  
  
```php  
use \MilesPong\Horizon\DynamicHorizonSupervisor;  
  
app()->make(DynamicHorizonSupervisor::class)->add('YOUR_MASTER_ID', function () {  
    return [  
        'supervisor-1' => [  
            'connection' => 'redis',  
            'queue' => ['foo'],  
            'balance' => 'auto',  
            'processes' => 10,  
            'tries' => 3,  
            'sleep' => 3,  
        ],  
        // 'supervisor-2', 'supervisor-3', ...  
    ];  
});  
```
