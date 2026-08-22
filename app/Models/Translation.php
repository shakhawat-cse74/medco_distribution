<?php

namespace App\Models;


use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Translation extends Model
{
    use HasFactory;
    
    protected $fillable = ['locale', 'group', 'key', 'value'];

    // Get Default Language (Cached)
    public static function getTrnaslactionsByLocale(string $locale)
    {
        return Cache::rememberForever("translations_{$locale}", function () use($locale) {
            return self::where('locale', $locale)
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->group . '.' . $item->key => $item->value];
                })
                ->toArray();
        });
    }

    public static function forgetCachedTranslations()
    {
        Cache::forget('translations_by_locale');
        Cache::forget('languages_list');
        $languages = Language::pluck('language')->toArray();
        foreach ($languages as $lang) {
            Cache::forget("translations_{$lang}");
        }
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'locale');
    }
}
