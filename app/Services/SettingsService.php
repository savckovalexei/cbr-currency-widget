<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    public function get(string $key, $default = null)
    {
        return Setting::get($key, $default);
    }

    public function set(string $key, $value): void
    {
        Setting::set($key, $value);
        
        // Автоматически сбрасываем кэш при изменении настроек виджета
        if (str_starts_with($key, 'widget_')) {
            Cache::tags(['rates'])->flush();
        }
    }

    public function getWidgetInterval(): int
    {
        return (int) $this->get('widget_update_interval', 60);
    }

    public function setWidgetInterval(int $seconds): void
    {
        $this->set('widget_update_interval', max(5, $seconds));
    }
}