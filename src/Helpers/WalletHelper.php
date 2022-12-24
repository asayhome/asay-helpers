<?php

namespace AsayHome\AsayHelpers\Helpers;

use AsayHome\AsayHelpers\Models\AsayPaymentsOperations;
use AsayHome\AsayHelpers\Models\UserModel;

class WalletHelper
{

    public static $deposit_operation = 1;
    public static $withdraw_operation = 2;


    public static function getUserInstance()
    {
        return UserModel::class;
    }

    public static function getUserWalletInstance($user, $wallet_name)
    {
        if (!$wallet_name) {
            return $user->wallet;
        }
        if (!$user->hasWallet($wallet_name)) {
            $wallet = $user->createWallet([
                'name' => ucfirst($wallet_name),
                'slug' => $wallet_name,
            ]);
        } else {
            $wallet = $user->getWallet($wallet_name);
        }
        return $wallet;
    }

    public static function getBalance($user_id, $wallet_name = null)
    {
        $user = UserModel::where('id', $user_id)->first();
        if ($user) {
            if ($wallet_name) {
                $wallet = self::getUserWalletInstance($user, $wallet_name);
            } else {
                $wallet = $user->wallet;
            }
            return $wallet->balanceFloat;
        }

        return 0;
    }

    public static function deposit($user_id, $amount, $meta, $wallet_name = null)
    {
        if (!is_array($meta)) {
            $meta = [
                'description' => $meta,
                'timestamp' => date('Y-m-d H:i:s'),
                'user_id' => $user_id
            ];
        }

        $user = UserModel::where('id', $user_id)->first();
        if ($user) {
            $wallet = self::getUserWalletInstance($user, $wallet_name);
            $wallet->depositFloat(floatval($amount), $meta);
            return true;
        }
        return false;
    }

    public static function withdraw($user_id, $amount, $meta, $wallet_name = null)
    {
        if (!is_array($meta)) {
            $meta = [
                'description' => $meta,
                'timestamp' => date('Y-m-d H:i:s'),
                'user_id' => $user_id
            ];
        }
        $user = UserModel::where('id', $user_id)->first();
        if ($user) {
            $wallet = self::getUserWalletInstance($user, $wallet_name);
            $wallet->forceWithdrawFloat(floatval($amount), $meta);
            return true;
        }
        return false;
    }

    public static function transfer(
        $from_user_id,
        $to_user_id,
        $amount,
        $meta,
        $from_user_wallet = null,
        $to_user_wallet = null
    )
    {
        if (!is_array($meta)) {
            $meta = [
                'description' => $meta,
                'timestamp' => date('Y-m-d H:i:s'),
                'user_id' => $from_user_id
            ];
        }

        $from_user = UserModel::where('id', $from_user_id)->first();
        $to_user = UserModel::where('id', $to_user_id)->first();
        if ($from_user) {
            $from_user_wallet = self::getUserWalletInstance($from_user, $from_user_wallet);
            $to_user_wallet = self::getUserWalletInstance($to_user, $to_user_wallet);
            $from_user_wallet->forceTransfer($to_user_wallet, floatval($amount), $meta);
        }
        return true;
    }


    public static function addDepositOperation($user_id, $order_id, $operation, $reason, $amount, $notes = '')
    {
        if ($amount > 0) {
            AsayPaymentsOperations::create([
                'user_id' => $user_id,
                'created_by' => auth()->check() ? auth()->user()->id : $user_id,
                'order_id' => $order_id,
                'operation' => $operation,
                'type' => PaymentsHelper::$deposit_type,
                'reason' => $reason,
                'amount' => $amount,
                'reference' => '',
                'details' => $notes,
                'gateway' => 'wallet',
                'status' => 'captured',
                'key_type' => '',
            ]);
        }
    }

    public static function addWithdrawOperation($user_id, $order_id, $operation, $reason, $amount, $notes = '')
    {
        if ($amount > 0) {
            WalletHelper::withdraw($user_id, $amount, [
                'balance' => $amount,
                'description' => 'Wallet withdraw operation',
                'created_by' => auth()->check() ? auth()->user()->id : $user_id,
                'timestamp' => date('Y-m-d H:i:s', time()),
            ]);

            AsayPaymentsOperations::create([
                'user_id' => $user_id,
                'created_by' => auth()->check() ? auth()->user()->id : $user_id,
                'order_id' => $order_id,
                'operation' => $operation,
                'type' => PaymentsHelper::$withdraw_type,
                'reason' => $reason,
                'amount' => $amount,
                'reference' => '',
                'details' => $notes,
                'gateway' => 'wallet',
                'status' => 'captured',
                'key_type' => '',
            ]);
        }
    }

    /**
     * next to be removed
     */

    public static function transferNotify($receiver_id, $subject = '', $body = '')
    {
        // $notify = new \App\Helpers\NotificationHelper('general', $receiver_id, '');
        // $notify->prepareData();
        // if (strlen($subject) > 0) {
        //     $notify->subject = $subject;
        // }
        // if (strlen($body) > 0) {
        //     $notify->body = $body;
        // }
        // $notify->send();
    }

    // public static function reschedulingOrder($order_id, $fees, $gateway = 'wallet')
    // {
    //     try {
    //         $order = Orders::where('id', $order_id)->first();
    //         if (floatval($order->owner->getBalance()) < $fees) {
    //             goto end;
    //         }
    //         WalletHelper::addWithdrawOperation(
    //             $order->created_by,
    //             $order->id,
    //             PaymentsHelper::$wallet_operation,
    //             PaymentsHelper::$reschedule_fees_reason,
    //             $fees,
    //             ''
    //         );
    //         $total_fees = OrdersHelper::addRescheduleFee($order, $fees, $gateway);
    //         $order_rescheduling_fee = getSetting('order_rescheduling_fee', 0);
    //         if ($total_fees >= $order_rescheduling_fee) {
    //             OrdersHelper::rescheduleExamination($order);
    //             return (object)array('success' => true, 'msg' => __('apps.rescheduling_applied'));
    //         } else {
    //             return (object)array('success' => false, 'remaining' => ($order_rescheduling_fee - $total_fees));
    //         }
    //     } catch (\Exception $e) {
    //         return (object)array('success' => false, 'msg' => $e->getMessage());
    //     }

    //     end:
    //     return (object)array('success' => false, 'msg' => __('apps.has_not_enough_balance'));
    // }
}
