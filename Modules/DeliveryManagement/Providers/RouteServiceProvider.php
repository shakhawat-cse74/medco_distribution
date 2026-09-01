<?php

namespace Modules\DeliveryManagement\Providers;

use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends \Illuminate\Foundation\Support\Providers\RouteServiceProvider
{
    protected string $moduleName = 'DeliveryManagement';

    protected string $moduleNamespace = 'Modules\DeliveryManagement\Http\Controllers';

    public function boot()
    {
        parent::boot();
    }

    public function map()
    {
        $this->mapWebRoutes();
    }

    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->moduleNamespace)
            ->group(module_path($this->moduleName, 'Routes/web.php'));
    }
}
