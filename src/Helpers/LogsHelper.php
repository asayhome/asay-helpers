<?php

namespace AsayHome\AsayHelpers\Helpers;

use Directory;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;

class LogsHelper
{
    public static $log_type_client_app = 'ClientApp';
    public static $log_type_provider_app = 'ProviderApp';

    public static function writeLog($dir, $fileName, $method, $message)
    {
        $max = 10000;
        // Get Monolog Instance
        $logger = new Logger($dir);
        $dir_full_path = storage_path('logs/' . $dir);
        if (!File::isDirectory($dir_full_path)) {
            File::makeDirectory($dir_full_path, 0777, true, true);
        }
        // Trim log file to a max length
        $path = storage_path('logs/' . $dir . '/' . $fileName . '.log');

        if (!file_exists($path)) {
            fopen($path, "w");
        }
        $lines = file($path);
        if (count($lines) >= $max) {
            file_put_contents($path, implode('', array_slice($lines, -$max, $max)));
        }
        // Define custom Monolog handler
        $handler = new StreamHandler($path, Logger::DEBUG);
        $handler->setFormatter(new LineFormatter(null, null, true, true));
        // Set defined handler and log the message
        $logger->setHandlers([$handler]);
        $logger->$method($message);
    }

    public static function clientAppLog($method, $message)
    {
        $fileName = 'clientapp-' . config('app.env') . '-' . date('Y-m-d') . '-log';
        self::writeLog(self::$log_type_client_app, $fileName, $method, $message);
    }
    public static function providerAppLog($method, $message)
    {
        $fileName = 'providerapp-' . config('app.env') . '-' . date('Y-m-d') . '-log';
        self::writeLog(self::$log_type_provider_app, $fileName, $method, $message);
    }
    public static function clientAppError($message)
    {
        self::clientAppLog('error', $message);
    }
    public static function providerAppError($message)
    {
        self::providerAppLog('error', $message);
    }
    public static function appError($subject, $message)
    {
        $details = [
            'created_at' => now()
        ];
        if (is_array($message)) {
            $details = array_merge($details, $message);
        } else {
            $details['message'] = $message;
        }

        Log::error($subject, $details);
    }
}
