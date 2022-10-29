<?php

namespace AsayHome\AsayHelpers\Helpers;

use AsayHome\AsayHelpers\Models\SettingsModel;

class AppHelper
{

    public static function saveSetting($key, $value)
    {
        if (!$key) {
            return false;
        }

        $sett = SettingsModel::where('key', $key)->first();
        if ($sett) {
            $sett->value = $value;
            $sett->save();

            return 'updated';
        }

        SettingsModel::create([
            'key' => $key,
            'value' => $value,
        ]);

        return 'added';
    }

    public static function getSettings($keys, $default = '')
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
    public static function setEnvValue(array $values)
    {
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);
        if (count($values) > 0) {
            foreach ($values as $envKey => $envValue) {

                if (preg_match('/\s/', $envValue)) {
                    $envValue = '\'' . $envValue . '\'';
                }

                $str .= "\n"; // In case the searched variable is in the last line without \n
                $keyPosition = strpos($str, "{$envKey}=");
                $endOfLinePosition = strpos($str, "\n", $keyPosition);
                $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

                // If key does not exist, add it
                if (!$keyPosition || !$endOfLinePosition || !$oldLine) {
                    $str .= "{$envKey}={$envValue}\n";
                } else {
                    $str = str_replace($oldLine, "{$envKey}={$envValue}", $str);
                }
            }
        }
        $str = substr($str, 0, -1);
        if (!file_put_contents($envFile, $str)) return false;
        return true;
    }

    public static function renderHTML($html)
    {
        $html = html_entity_decode($html);
        $html = strip_tags($html);
        return $html;
    }

    function generateOTP($length, $HasChars = true, $hasNums = true)
    {
        $alpha = '';
        if ($HasChars) {
            $alpha .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        if ($hasNums) {
            $alpha .= '0123456789';
        }
        $charactersLength = strlen($alpha);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $alpha[rand(0, $charactersLength - 1)];
        }
        return  $randomString;
    }
}
