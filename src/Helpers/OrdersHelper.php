<?php

namespace AsayHome\AsayHelpers\Helpers;

use AsayHome\AsayHelpers\Models\AsayPaymentsOperations;

class OrdersHelper
{

    public static function getPaymentsDetails(
        $order_id,
        $created_by,
        $order_amount,
        $order_tax,
        $order_provider_percentage,
        $order_application_percentage
    ) {


        $reason = PaymentsHelper::$accepting_reason;



        $details = [];
        $remaining_amount = floatval($order_amount);
        $details['order_amount'] = floatval($order_amount);
        // calc order tax value
        $order_tax_amount = $remaining_amount / ('1.' . $order_tax) * ('0.' . $order_tax);
        $details['tax_amount'] = round($order_tax_amount);
        $details['tax_percentage'] = $order_tax;
        // calc amount after discount the tax
        $remaining_amount -= $order_tax_amount;
        $details['amount_after_discount_tax'] = round($remaining_amount);
        // calc the provider and application amount

        $provider_app_percentage = 100 / ($order_provider_percentage + $order_application_percentage);
        $provider_percentage = $provider_app_percentage * ($order_provider_percentage / 100);
        $application_percentage = $provider_app_percentage * ($order_application_percentage  / 100);
        $details['provider_app_amount'] = [
            'order_provider_percentage' => $order_provider_percentage,
            'order_application_percentage' => $order_application_percentage,
            'provider_app_percentage' => round($provider_app_percentage, 2),
            'provider_percentage' => round($provider_percentage, 2),
            'application_percentage' => round($application_percentage, 2),
            'provider_amount' => round($provider_percentage * $remaining_amount),
            'application_amount' => round($application_percentage * $remaining_amount),
        ];
        // calc amount after discount coupons
        $details['discount_value'] = 0;
        $coupons_payments = AsayPaymentsOperations::where('order_id', $order_id)
            ->where('reason', $reason)
            ->where('operation', PaymentsHelper::$coupons_operation)
            ->where('user_id', $created_by)
            ->get();
        if ($coupons_payments) {
            foreach ($coupons_payments as $payment) {
                try {
                    $coupon_details = $payment->details;
                    if ($coupon_details['discount_type'] == CouponsHelper::$coupons_discount_by_percentage) {
                        $discount_value = round(floatval($remaining_amount) * (floatval($coupon_details['discount_value']) / 100));
                        $remaining_amount -= $discount_value;
                        if ($coupon_details['discount_from'] == CouponsHelper::$coupons_discount_from_application) {
                            $details['provider_app_amount']['application_amount'] -= round($application_percentage * $remaining_amount);
                        } else if ($coupon_details['discount_from'] == CouponsHelper::$coupons_discount_from_provider) {
                            $details['provider_app_amount']['provider_amount'] -= round($provider_percentage * $remaining_amount);
                        } else {
                            $remaining_amount = round(floatval($remaining_amount) - (floatval($remaining_amount) * (floatval($coupon_details['discount_value']) / 100)));
                            $details['provider_app_amount']['application_amount'] -= round($application_percentage * $remaining_amount);
                            $details['provider_app_amount']['provider_amount'] -= round($provider_percentage * $remaining_amount);
                        }
                    } else {
                        $details['discount_value'] += floatval($coupon_details['discount_value']);
                        $discount_value = round(floatval($remaining_amount) - floatval($coupon_details['discount_value']));
                    }
                    /**Next if the orginal code */
                    // if ($coupon_details['discount_type'] == CouponsHelper::$coupons_discount_by_percentage) {
                    //     $discount_value = round(floatval($remaining_amount) * (floatval($coupon_details['discount_value']) / 100));
                    //     if ($coupon_details['discount_from'] == CouponsHelper::$coupons_discount_from_application) {
                    //         $details['provider_app_amount']['application_amount'] -= round($application_percentage * ($remaining_amount - $discount_value));
                    //     } else if ($coupon_details['discount_from'] == CouponsHelper::$coupons_discount_from_provider) {
                    //         $details['provider_app_amount']['provider_amount'] -= round($provider_percentage * ($remaining_amount - $discount_value));
                    //     } else {
                    //         $remaining_amount = round(floatval($remaining_amount) - (floatval($remaining_amount) * (floatval($coupon_details['discount_value']) / 100)));
                    //         $details['provider_app_amount']['application_amount'] -= round($application_percentage * ($remaining_amount - $discount_value));
                    //         $details['provider_app_amount']['provider_amount'] -= round($provider_percentage * ($remaining_amount - $discount_value));
                    //     }
                    // } else {
                    //     $details['discount_value'] += floatval($coupon_details['discount_value']);
                    //     $discount_value = round(floatval($remaining_amount) - floatval($coupon_details['discount_value']));
                    // }
                    $details['coupons'][] = [
                        'coupon_code' => $coupon_details['code'],
                        'coupon_type' => $coupon_details['discount_type'] == 1 ? 'by_percentage' : 'by_value',
                        'coupon_value' => $coupon_details['discount_value'],
                        'coupon_discount_from' => $coupon_details['discount_from'],
                        'discount_value' => $discount_value,
                        'remaining_amount' => round($remaining_amount, 2)
                    ];
                } catch (\Exception $exception) {
                }
            }
        }
        // calc amount with tax
        $details['amount_tax'] = round(floatval($remaining_amount) * floatval('0.' . $order_tax));
        $remaining_amount = floatval($remaining_amount) + (floatval($remaining_amount) * floatval('0.' . $order_tax));
        $details['amount_with_tax'] = round(floatval($remaining_amount));
        $details['remaining_amount'] = round(floatval($remaining_amount));
        // calc other payments operations
        $others_payments = AsayPaymentsOperations::where('order_id', $order_id)
            ->where('reason', $reason)
            ->where('operation', '<>', PaymentsHelper::$coupons_operation)
            ->where('user_id', $created_by)
            ->get();
        if ($others_payments) {
            foreach ($others_payments as $payment) {
                $details['remaining_amount'] -=  round(floatval($payment->amount));
                $details['other_payments'][] = [
                    'gateway' => $payment->gateway,
                    'payment_amount' => round(floatval($payment->amount)),
                    'remaining_amount' => $details['remaining_amount']
                ];
            }
        }
        return $details;
    }
}
