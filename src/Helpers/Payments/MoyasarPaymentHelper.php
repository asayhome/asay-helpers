<?php

namespace AsayHome\AsayHelpers\Helpers\Payments;

class MoyasarPaymentHelper
{
    public static $status_paid = 'paid';
    public static $status_fail = 'failed';
    public static $status_voided = 'voided';

    public static function getValidAmount($amount, $currency = 'SAR')
    {
        if ($currency == 'SAR') {
            return $amount * 100; // 10 SAR = 10 * 100 Halalas
        } else if ($currency == 'KWD') {
            return $amount * 1000;   // 10 KWD = 10 * 1000 Fils
        } else if ($currency == 'JPY') {
            return $amount; // 10 JPY = 10 JPY (Japanese Yen does not have fractions)
        }
        dd('not allowed currency, please contact technical support');
    }

    public static function getKey()
    {
        $settings = getSettings([
            'moyasar_running_environment',
            'moyasar_api_test_secret_key',
            'moyasar_api_test_publishable_key',
            'moyasar_api_production_secret_key',
            'moyasar_api_production_publishable_key'
        ], '');
        if ($settings['moyasar_running_environment'] == 'production') {
            return $settings['moyasar_api_production_publishable_key'];
        } else {
            return $settings['moyasar_api_test_publishable_key'];
        }
    }
}
