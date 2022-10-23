<?php

namespace AsayHome\AsayHelpers\Helpers;

use App\Models\Coupons;
use App\Models\Orders;
use AsayHome\AsayHelpers\Models\AsayPaymentsOperations;

class CouponsHelper
{
    public static $coupons_all_users = '*';
    public static $coupons_discount_by_percentage = 1;
    public static $coupons_discount_by_value = 2;

    public static $coupons_discount_from_application = 1;
    public static $coupons_discount_from_provider = 2;
    public static $coupons_discount_from_application_and_provider = 3;


    public static $coupons_affiliate_from_application = 1;


    public static function getDiscountFromList()
    {

        return [
            self::$coupons_discount_from_application => __('Application'),
            self::$coupons_discount_from_provider => __('Provider'),
            self::$coupons_discount_from_application_and_provider => __("Application") + " " + __("And") + " " + __("Provider"),
        ];
    }

    public static function getAffiliateFromList()
    {
        return [
            self::$coupons_affiliate_from_application => __('Application'),
        ];
    }

    public static function getCouponValue($coupon, $amount)
    {
        if ($coupon->discount_type == self::$coupons_discount_by_percentage) {
            return $amount * ($coupon->discount_value / 100);
        } else {
            return $amount - $coupon->discount_value;
        }
    }


    public static function applyCoupon($coupon_code, $user_id, $order_id, $order_remaining_amount)
    {

        $coupon = self::getUserCoupons($user_id, $coupon_code);
        if ($coupon) {
            /**
             * 1- calc order remaining amount
             */
            $amount = $order_remaining_amount;
            /**
             * 2- calc and register coupon value
             */
            $coupon_value = self::getCouponValue($coupon, $order_remaining_amount);
            $meta = [
                'user_id' => $user_id,
                'created_by' => (auth()->check() ? auth()->user()->id : $user_id),
                'order_id' => $order_id,
                'operation' => PaymentsHelper::$coupons_operation,
                'operation_id' => $coupon_code,
                'reference' => '',
                'details' => json_encode($coupon),
                'gateway' => 'coupons',
                'status' => 'captured',
                'key_type' => '',
            ];
            if ($coupon_value > $order_remaining_amount) {
                $coupon_remaining_value = $coupon_value - $order_remaining_amount;
                $meta['type'] = PaymentsHelper::$deposit_type;
                $meta['reason'] = PaymentsHelper::$remaining_coupon_value_reason;
                $meta['amount'] = $coupon_remaining_value;
                WalletHelper::deposit($user_id, $coupon_remaining_value, $meta);
                AsayPaymentsOperations::create($meta);
            }

            $meta['type'] = PaymentsHelper::$withdraw_type;
            $meta['reason'] = PaymentsHelper::$accepting_reason;
            $meta['amount'] = $coupon_value;

            AsayPaymentsOperations::create($meta);

            return true;
        }
        return false;
    }


    public static function getUserCoupons($user_id, $coupon_code = null, $search_in_marketers_coupons = true)
    {
        /**
         * check it in coupons
         */
        $ids = [];
        foreach (Coupons::where('publish', true)
            ->where('from_date', '<=', \Carbon\Carbon::now()->toDateString())
            ->where('to_date', '>=', \Carbon\Carbon::now()->toDateString())->cursor() as $coupon) {
            $users = [];
            if ($coupon->users && is_array(json_decode($coupon->users, true))) {
                $users = json_decode($coupon->users, true);
            }
            if (in_array($user_id, $users) || in_array(self::$coupons_all_users, $users)) {
                //check to be not userd before by current user
                $user_used_coupon = AsayPaymentsOperations::where('user_id', $user_id)
                    ->where('operation', PaymentsHelper::$coupons_operation)
                    ->where('operation_id', $coupon->code)
                    ->count();
                if ($user_used_coupon < $coupon->user_numbers_of_use) {
                    array_push($ids, $coupon->id);
                }
            }
        }
        $query = Coupons::where('publish', true)->whereIn('id', $ids);
        if ($coupon_code) {
            $query = $query->whereRaw('lower(code) = ?', strtolower($coupon_code));
        }
        $coupons = $query->select([
            'name',
            'short_description as description',
            'image',
            'from_date',
            'to_date',
            'code',
            'discount_type',
            'discount_value',
            'discount_from',
            'publish',
        ]);

        if ($coupon_code) {
            return $coupons->first();
        }
        return $coupons->get();
    }
}
