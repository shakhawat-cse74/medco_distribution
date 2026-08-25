<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

if (!function_exists('normalize_to_sql_datetime')) {
    function normalize_to_sql_datetime($input, $useCurrentTime = false)
    {
        if (empty($input)) {
            return Carbon::now()->format('Y-m-d H:i:s');
        }

        $input = trim($input);

        // Replace multiple possible separators with "-"
        $normalized = preg_replace('/[\/\.\s]+/', '-', $input);

        // Formats to test (you can add more if needed)
        $formats = [
            'd-m-Y',
            'd/m/Y',
            'd.m.Y',
            'm-d-Y',
            'm/d/Y',
            'm.d.Y',
            'Y-m-d',
            'Y/m/d',
            'Y.m.d',
        ];

        foreach ($formats as $fmt) {
            try {
                $date = Carbon::createFromFormat($fmt, $normalized);

                if ($date !== false) {
                    if ($useCurrentTime) {
                        // inject current time if only date provided
                        $date->setTimeFrom(Carbon::now());
                    }
                    return $date->format('Y-m-d H:i:s');
                }
            } catch (\Exception $e) {
                // just continue to next format
            }
        }

        // fallback: try Carbon::parse (loose parsing)
        try {
            $date = Carbon::parse($input);
            if ($useCurrentTime) {
                $date->setTimeFrom(Carbon::now());
            }
            return $date->format('Y-m-d') . ' ' . date('H:i:s');
        } catch (\Exception $e) {
            // totally failed → return current datetime
            return Carbon::now()->format('Y-m-d H:i:s');
        }
    }
}

if (!function_exists('format_currency')) {
    function format_currency($amount, $currency = null, $position = null, $decimal = null, $mask = false)
    {
        if ($mask) {
            return '****';
        }

        $currency = $currency ?? config('currency');
        $position = $position ?? config('currency_position');
        $decimal  = $decimal ?? config('decimal');

        $formatted = number_format((float) $amount, $decimal, '.', '');

        if ($position == 'prefix') {
            return $currency . ' ' . $formatted;
        }

        return $formatted . ' ' . $currency;
    }
}

if (! function_exists('gen_setting')) {
    function gen_setting()
    {
        $setting = Cache::remember('general_setting', 60 * 60 * 24 * 365, function () {
            return DB::table('general_settings')->latest()->first();
        });

        if (!$setting || !is_object($setting)) {
            $setting = (object)[];
        }

        // Ensure default properties always exist on the stdClass
        if (!isset($setting->site_title)) $setting->site_title = 'BanglaSoft';
        if (!isset($setting->site_logo)) $setting->site_logo = 'banglasoft_logo.png';
        if (!isset($setting->favicon)) $setting->favicon = 'banglasoft_logo.png';
        if (!isset($setting->font_css)) $setting->font_css = null;
        if (!isset($setting->auth_css)) $setting->auth_css = null;
        if (!isset($setting->pos_css)) $setting->pos_css = null;
        if (!isset($setting->custom_css)) $setting->custom_css = null;
        if (!isset($setting->is_rtl)) $setting->is_rtl = 0;
        if (!isset($setting->currency)) $setting->currency = '$';
        if (!isset($setting->currency_position)) $setting->currency_position = 'prefix';
        if (!isset($setting->decimal)) $setting->decimal = 2;
        if (!isset($setting->date_format) || empty($setting->date_format)) $setting->date_format = 'd-m-Y';
        if (!isset($setting->theme)) $setting->theme = 'default.css';
        if (!isset($setting->modules)) $setting->modules = '';

        return $setting;
    }
}

if (! function_exists('get_active_warehouses')) {
    function get_active_warehouses()
    {
        return Cache::remember('warehouse_list', 60 * 60 * 24 * 365, function () {
            return DB::table('warehouses')->where('is_active', true)->get();
        });
    }
}

if (! function_exists('get_active_units')) {
    function get_active_units()
    {
        return Cache::remember('unit_list', 60 * 60 * 24 * 365, function () {
            return DB::table('units')->where('is_active', true)->get();
        });
    }
}

if (! function_exists('get_active_taxes')) {
    function get_active_taxes()
    {
        return Cache::remember('tax_list', 60 * 60 * 24 * 365, function () {
            return DB::table('taxes')->where('is_active', true)->get();
        });
    }
}

if (! function_exists('get_active_categories')) {
    function get_active_categories()
    {
        return Cache::remember('categories_list', 60 * 60 * 24 * 365, function () {
            return DB::table('categories')->where('is_active', true)->get();
        });
    }
}
