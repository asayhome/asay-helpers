<?php

namespace AsayHome\AsayHelpers\Helpers;

use AsayHome\AsayHelpers\Models\SettingsModel;

class AppHelper
{

    public static function getSettings($keys, $default)
    {
        if (!is_array($keys)) {
            $result = SettingsModel::where('key', $keys)->select('key', 'value')->first();
            if (!$result) {
                return $default;
            }
            return $result->value ? $result->value : $default;
        }
        $results = SettingsModel::whereIn('key', $keys)->select('key', 'value')->get();
        $settings = [];
        foreach ($results as $set) {
            $settings[$set->key] = $set->value ? $set->value : $default;
        }
        /**
         * add not setted values
         */
        foreach ($keys as $key) {
            if (!in_array($key, array_keys($settings))) {
                $settings[$key] = $default;
            }
        }
        return $settings;
    }
}
