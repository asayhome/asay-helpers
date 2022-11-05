<?php


namespace AsayHome\AsayHelpers\Helpers;

use AsayHome\AsayHelpers\Helpers\AppHelper;

class SlimNotifier
{
    public  static $success = 'success';
    public  static $warning = 'warning';
    public  static $error = 'error';
    public  static $envelope = 'envelope';

    public static function prepereNotifyData($type, $title, $message, $duration = '3000')
    {
        return array(
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'duration' => $duration,
            'theme' => AppHelper::getSettings('app_default_notifier', 'slime'),   // slime,flashjs
        );
    }
}
