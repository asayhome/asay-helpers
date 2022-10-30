<?php

namespace AsayHome\AsayHelpers\Helpers;

use Illuminate\Support\Carbon;

class TimestampHelper
{
    public static function getLocaledTimestamp($timestamp)
    {
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp);
        $userTimeZone = config('app.timezone', 'UTC');
        $date->setTimezone($userTimeZone);
        return $date;
    }
    public static function getRemainingTime($timestamp, $from = 'now')
    {
        if ($from == 'now') {
            $now = Carbon::now(config('app.timezone', 'UTC'));
        } else {
            $now = Carbon::parse($from, config('app.timezone', 'UTC'));
        }
        return $now->diff(Carbon::parse($timestamp, config('app.timezone', 'UTC')))->format('%H:%I:%S');
        // $timestamp = Carbon::parse($timestamp);
        // return $timestamp->diffForHumans($now, true, true, 2);
    }
}
