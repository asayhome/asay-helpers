<?php


namespace App\Helpers;

use Infty\AppSettings\AppSettingsHelper;

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
            'theme' => AppSettingsHelper::getSetting('app_default_notifier', 'slime'),   // slime,flashjs
        );
    }
}
