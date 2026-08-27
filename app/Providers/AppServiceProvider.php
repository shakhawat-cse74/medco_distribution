<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use App\Models\Translation;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\App;
use Stancl\Tenancy\Events\TenancyBootstrapped;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\GeneralSetting;
use App\Services\PermissionService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Path relative to the root of your project
        $helperFile = app_path('Helpers/helpers.php');

        if (file_exists($helperFile)) {
            require_once($helperFile);
        }
    }


    public function boot()
    {
        \Illuminate\Database\Eloquent\Relations\Relation::morphMap([
            'sale' => 'App\Models\Sale',
        ]);
        Schema::defaultStringLength(191);
        $this->app->bind(\App\ViewModels\ISmsModel::class, \App\ViewModels\SmsModel::class);

        try {
            if (Schema::hasTable('general_settings')) {
                config(['addons' => gen_setting()->modules ?? '']);
            }
        } catch (\Exception $e) {
        }

        if (app()->runningInConsole()) {
            return;
        }

        $translationLogic = function () {
            try {
                if (!DB::connection()->getDatabaseName()) {
                    return;
                }
            } catch (\Exception $e) {
                // Skip logic if DB connection fails
                return;
            }

            try {
                if (isset($_COOKIE['language'])) {
                    App::setLocale($_COOKIE['language']);
                } elseif (Schema::hasTable('languages')) {
                    $language = DB::table('languages')->where('is_default', true)->first();
                    App::setLocale($language->language ?? 'en');
                } else {
                    App::setLocale('en');
                }

                if (Schema::hasTable('translations')) {
                    $currentLocale = App::getLocale();

                    $translations = Cache::rememberForever("translations_{$currentLocale}", function () use ($currentLocale) {
                        return \App\Models\Translation::getTrnaslactionsByLocale($currentLocale);
                    });

                    if (!empty($translations)) {
                        app('translator')->addLines($translations, $currentLocale);
                    }
                }
            } catch (\Exception $e) {
                // Optional: log the error
                // Log::error($e->getMessage());
            }
        };

        $permissionLogic = function () {
            Blade::if('can', function ($permission) {
                return in_array($permission, app(PermissionService::class)->getAuthenticatedUserPermissions());
            });

            Blade::if('canany', function ($permissions) {
                $requested = is_array($permissions) ? $permissions : explode(',', $permissions);
                $requested = array_map('trim', $requested);

                return !empty(array_intersect($requested, app(PermissionService::class)->getAuthenticatedUserPermissions()));
            });
        };

        $maintenanceCheck = function () {
            // general_settings table না থাকলে → ignore
            if (!Schema::hasTable('general_settings')) {
                return;
            }

            $general_setting = GeneralSetting::latest()->first();

            // IP নাই → maintenance OFF
            if (!$general_setting || empty($general_setting->maintenance_allowed_ips)) {
                return;
            }

            $allowedIps = array_map(
                'trim',
                explode(',', $general_setting->maintenance_allowed_ips)
            );

            if (!in_array(request()->ip(), $allowedIps)) {
                abort(503); // Laravel default page
            }
        };

        $viewComposerLogic = function () {
            View::composer(
                ['backend.layout.0main_rtl', 'backend.layout.main', 'backend.sale.pos'],
                function ($view) {

                    $general_setting = gen_setting();

                    $alert_product = Cache::remember('alert_product_count', 60 * 15, function () {
                        return DB::table('products')
                            ->where('is_active', true)
                            ->whereColumn('alert_quantity', '>', 'qty')
                            ->count();
                    });

                    $dso_alert_product_no = Cache::remember(
                        'dso_alert_count_' . date('Y-m-d'),
                        60 * 15,
                        function () {
                            $record = DB::table('dso_alerts')
                                ->select('number_of_products')
                                ->whereDate('created_at', date('Y-m-d'))
                                ->first();

                            return $record?->number_of_products ?? 0;
                        }
                    );

                    $days = (int) ($general_setting->expiry_alert_days ?? 0);

                    $expire_alert_products = Cache::remember(
                        'expire_alert_count_' . $days,
                        60 * 15,
                        function () use ($days) {
                            return DB::table('product_batches')
                                ->join('products', 'products.id', '=', 'product_batches.product_id')
                                ->where('products.is_active', true)
                                ->where('product_batches.qty', '>', 0)
                                ->whereDate(
                                    'product_batches.expired_date',
                                    '<=',
                                    now()->addDays($days)->format('Y-m-d')
                                )
                                ->count();
                        }
                    );

                    $view->with([
                        'alert_product'         => $alert_product,
                        'dso_alert_product_no'  => $dso_alert_product_no,
                        'expire_alert_products' => $expire_alert_products,
                    ]);
                }
            );
        };

        if (config('database.connections.saleprosaas_landlord')) {
            ///new code for superadmin//
            if (!app()->bound('tenancy')) {
                $locale = null;
                if (!request()->is('superadmin*') && isset($_COOKIE['frontend_language'])) {
                    $locale = $_COOKIE['frontend_language'];
                } elseif (config('database.connections.saleprosaas_landlord.database') && Schema::hasTable('languages')) {
                    // Fallback to default language
                    $default_language = DB::table('languages')->where('is_default', true)->first();
                    $locale = $default_language->code ?? 'en';
                } else {
                    $locale = 'en';
                }

                // Finally, set the app locale
                App::setLocale($locale);

                // Check if language file exists
                $langFile = resource_path("lang/{$locale}.php");
                if (!file_exists($langFile)) {
                    $langFile = resource_path("lang/master.php");
                }

                $transData = include $langFile; // loads the array
                $translations = [];
                foreach ($transData as $group => $items) {
                    foreach ($items as $key => $value) {
                        $translations["{$group}.{$key}"] = $value;
                    }
                }
                // Merge translations into Laravel's translator
                app('translator')->addLines($translations, $locale);
            }
            ///new code for superadmin//

            Event::listen(TenancyBootstrapped::class, function () use ($translationLogic, $permissionLogic, $maintenanceCheck, $viewComposerLogic) {
                $translationLogic();
                $permissionLogic();
                $maintenanceCheck();
                $viewComposerLogic();
            });
        } else {
            if (empty(env('DB_DATABASE'))) {
                if (!request()->is('install/*')) {
                    redirect('/install/step-1')->send();
                }
                return;
            }
            $translationLogic();
            $permissionLogic();
            $maintenanceCheck();
            $viewComposerLogic();
        }
    }
}
