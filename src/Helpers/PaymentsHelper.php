<?php


namespace AsayHome\AsayHelpers\Helpers;

use App\Helpers\OrdersHelper;
use AsayHome\AsayHelpers\Models\AsayPaymentsOperations;

class PaymentsHelper
{
    /**
     * Operations
     */
    public static $manager_operation = 1;
    public static $tap_payment_operation = 2;
    public static $wallet_operation = 3;
    public static $coupons_operation = 4;
    public static $moyasar_payment_operation = 5;

    public static $payment_refunded_status = 'refunded';
    public static $payment_voided_status = 'voided';


    public static function getOperations(): array
    {
        return [
            self::$manager_operation => __('Manager'),
            self::$tap_payment_operation => __('Tap payment'),
            self::$moyasar_payment_operation => __('Moyasar payment gateway'),
            self::$wallet_operation => __('Wallet'),
            self::$coupons_operation => __('Coupons')
        ];
    }

    /**
     * Types
     */
    public static $deposit_type = 1;
    public static $withdraw_type = 2;

    public static function getTypes(): array
    {
        return [
            self::$deposit_type => __('Deposit'),
            self::$withdraw_type => __('Withdraw')
        ];
    }

    /**
     * reasons
     */

    public static $accepting_reason = 1;
    public static $remaining_coupon_value_reason = 2;
    public static $discount_reason = 3;
    public static $wallet_deposit_reason = 4;
    public static $wallet_gift_reason = 5;
    public static $order_fees_reason = 6;
    public static $canceling_order_reason = 7;
    public static $marketing_order_reason = 8;
    public static $security_deposit_reason = 9;
    public static $wallet_withdraw_reason = 10;
    public static $reschedule_fees_reason = 11;
    public static $void_amount_reason = 12;
    public static $refund_amount_reason = 13;
    public static $rejecting_order_reason = 14;
    public static $transfer_to_tyqn_account_reason = 15;

    public static function getReasons(): array
    {
        return [
            self::$accepting_reason => __('Accepting offer'),
            self::$remaining_coupon_value_reason => __('Remaining coupon value'),
            self::$discount_reason => __('Discount'),
            self::$wallet_deposit_reason => __('Wallet deposit'),
            self::$wallet_gift_reason => __('Compensation'),
            self::$order_fees_reason => __('Order fees'),
            self::$canceling_order_reason => __('Canceling order'),
            self::$marketing_order_reason => __('Marketing order'),
            self::$security_deposit_reason => __('Pay a security deposit'),
            self::$wallet_withdraw_reason => __('Wallet withdraw'),
            self::$reschedule_fees_reason => __('Reschedule fees'),
            self::$void_amount_reason => __('Void amount'),
            self::$refund_amount_reason => __('Refund amount'),
            self::$rejecting_order_reason => __('Rejecting order'),
            self::$transfer_to_tyqn_account_reason => __('Transfer to tyaqan account')
        ];
    }


    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public static function getDefaultMetaArray(): array
    {
        return [
            'requested_by' => 'backend',
            'operation' => '',
            'operation_type' => 'deposit',
            'gateway' => '',
            'amount' => '',
            'reason' => '',
            'user_id' => '',
            'created_by' => '',
            'description' => '',
            'send_user_alert' => '',
            'alert_drivers' => '',
            'add_note_to_alert' => '',
            'back_url' => '',
        ];
    }


    public static function doWalletDepositOperation(
        $user_id,
        $amount,
        $description,
        $gateway,
        $operation,
        $operation_id,
        $payment_reference,
        $payment_status,
        $created_by,
        $details
    )
    {
        WalletHelper::deposit($user_id, $amount, [
            'balance' => $amount,
            'description' => $description,
            'user_id' => $user_id,
            'created_by' => $created_by,
            'timestamp' => date('Y-m-d H:i:s', time()),
        ]);
        try {
            AsayPaymentsOperations::create([
                'user_id' => $user_id,
                'created_by' => $created_by,
                'order_id' => null,
                'operation' => $operation,
                'operation_id' => $operation_id,
                'type' => self::$deposit_type,
                'reason' => self::$wallet_deposit_reason,
                'amount' => $amount,
                'reference' => $payment_reference,
                'gateway' => $gateway,
                'details' => is_array($details) ? json_encode($details) : $details,
                'status' => $payment_status,
            ]);
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    }

    public static function doSendingOrderOperation(
        $order,
        $amount,
        $gateway,
        $user_id,
        $payment_reference,
        $payment_status,
        $operation,
        $operation_id,
        $created_by,
        $details
    )
    {
        // (1): important: register the payment firstly
        AsayPaymentsOperations::create([
            'user_id' => $user_id,
            'created_by' => $created_by,
            'order_id' => $order->id,
            'operation' => $operation,
            'type' => self::$deposit_type,
            'reason' => self::$accepting_reason,
            'amount' => $amount,
            'reference' => $payment_reference,
            'gateway' => $gateway,
            'operation_id' => $operation_id,
            'details' => is_array($details) ? json_encode($details) : $details,
            'status' => $payment_status,
        ]);
        // (2): change order status to sending
        OrdersHelper::changeOrderToSend($order);
    }

    public static function doSecurityDepositOperation(
        $user,
        $amount,
        $gateway,
        $created_by,
        $operation,
        $operation_id,
        $payment_reference,
        $payment_status,
        $details
    )
    {
        $security_deposit = $amount;
        if ($user->security_deposit) {
            $security_deposit += $user->security_deposit;
        }
        $user->security_deposit = $security_deposit;
        $user->save();
        AsayPaymentsOperations::create([
            'user_id' => $user->id,
            'created_by' => $created_by,
            'order_id' => null,
            'operation' => $operation,
            'type' => self::$deposit_type,
            'reason' => self::$security_deposit_reason,
            'amount' => $amount,
            'reference' => $payment_reference,
            'gateway' => $gateway,
            'operation_id' => $operation_id,
            'details' => is_array($details) ? json_encode($details) : $details,
            'status' => $payment_status,
        ]);
    }

    // public static function doRescheduleFeesOperation(
    //     $order,
    //     $created_by,
    //     $amount,
    //     $gateway,
    //     $operation,
    //     $operation_id,
    //     $payment_reference,
    //     $payment_status,
    //     $details

    // ): object {

    //     $total_fees = OrdersHelper::addRescheduleFee($order, $amount, $gateway);
    //     PaymentsOperations::create([
    //         'user_id' => $order->created_by,
    //         'created_by' => $created_by,
    //         'order_id' => $order->id,
    //         'operation' => $operation,
    //         'type' => PaymentsHelper::$deposit_type,
    //         'reason' => PaymentsHelper::$reschedule_fees_reason,
    //         'amount' => $amount,
    //         'gateway' => $gateway,
    //         'operation_id' => $operation_id,
    //         'reference' => $payment_reference,
    //         'details' => is_array($details) ? json_encode($details) : $details,
    //         'status' => $payment_status,
    //     ]);
    //     $order_rescheduling_fee = getSetting('order_rescheduling_fee', 0);
    //     if ($total_fees >= $order_rescheduling_fee) {
    //         OrdersHelper::rescheduleExamination($order);
    //         return (object)['success' => true, 'msg' => __('apps.rescheduling_applied')];
    //     } else {
    //         return (object)['success' => false, 'remaining' => ($order_rescheduling_fee - $total_fees)];
    //     }
    // }

    public static function doPaymentAlert(
        $send_user_alert,
        $user_id,
        $body,
        $amount,
        $add_note_to_alert,
        $description,
        $alert_drivers
    )
    {
        if ($send_user_alert == 1) {
            // $notify = new NotificationHelper('general', $user_id);
            // $notify->template = 'general';
            // $notify->model = 'general';
            // $notify->model_id = $user_id;
            // $notify->prepareData();
            // $notify->subject = __('Financial statements');
            // $notify->body = __($body);
            // $notify->body .= ':' . $amount . ' ' . __('SAR');
            // if ($add_note_to_alert == 1) {
            //     $notify->body .= ', ' . $description;
            // }
            // $notify->drivers = $alert_drivers;
            // $notify->send();
        }
    }


    // public static function cancelPaidAmount(
    //     $order,
    //     $with_client_amount,
    //     $with_examiner_amount,
    //     $canceled_by
    // ) {


    //     $is_free_canceling = is_string(OrdersHelper::calc_free_cancel_period($order, getSetting('orders_free_cancellation_period', 0)));

    //     /**
    //      * examiner manipulation
    //      */
    //     if ($with_examiner_amount && $order->offer) {
    //         if ($order->company_id) { // its company orders
    //             $canceling_amount = getSetting('companies_orders_examiner_canceling_discount_value', 0);
    //         } else {
    //             $canceling_amount = $order->offer->offer_amount * (getSetting('order_canceling_examiner_percentage', 0) / 100);
    //         }
    //         // companies orders not contain free canceling time
    //         if ($canceling_amount > 0 && (!$is_free_canceling || $order->company_id)) {
    //             WalletHelper::withdraw($order->examiner->id, $canceling_amount, '');
    //             WalletHelper::addWithdrawOperation(
    //                 $order->examiner->id,
    //                 $order->id,
    //                 PaymentsHelper::$wallet_operation,
    //                 PaymentsHelper::$canceling_order_reason,
    //                 $canceling_amount,
    //                 ''
    //             );
    //         }
    //     }

    //     /**
    //      * calc client canceling amount
    //      */
    //     $canceling_amount = 0;

    //     if (!$is_free_canceling && $with_client_amount && $order->offer) { // its not a free time
    //         $canceling_amount = $order->offer->offer_amount * (getSetting('order_canceling_client_percentage', 0) / 100);
    //     }

    //     $payments = PaymentsOperations::where('order_id', $order->id)
    //         ->where('created_by', $order->created_by)
    //         ->where('reason', PaymentsHelper::$accepting_reason)
    //         ->get();

    //     $tap = new TapPaymentHelper();

    //     if ($is_free_canceling) {
    //         $reason = PaymentsHelper::$void_amount_reason;
    //     } else {
    //         $reason = PaymentsHelper::$refund_amount_reason;
    //     }


    //     $tyqn_plus_user = UserHelper::getTyqnPlusUser();

    //     /**
    //      * remove already previous canceled amount
    //      */
    //     $prev_canceled_amount = PaymentsOperations::where('user_id', $tyqn_plus_user->id)
    //         ->where('order_id', $order->id)
    //         ->where('type', PaymentsHelper::$deposit_type)
    //         ->where('reason', PaymentsHelper::$canceling_order_reason)->sum('amount');
    //     if ($prev_canceled_amount) {
    //         $canceling_amount -= $prev_canceled_amount;
    //     }

    //     foreach ($payments as $payment) {

    //         if (!in_array(strtolower($payment->status), [self::$payment_refunded_status, self::$payment_voided_status])) {

    //             $canceled_amount = $canceling_amount;
    //             if ($payment->amount >= $canceling_amount) {
    //                 $amount = $payment->amount - $canceling_amount;
    //                 $canceling_amount = 0;
    //             } else {
    //                 $canceling_amount = $canceling_amount - $payment->amount;
    //                 $canceled_amount = $payment->amount;
    //                 $amount = 0;
    //             }

    //             if ($amount > 0) {
    //                 if ($payment->gateway == 'wallet') {
    //                     WalletHelper::deposit($order->owner->id, $amount, '');
    //                     WalletHelper::addDepositOperation(
    //                         $order->owner->id,
    //                         $order->id,
    //                         PaymentsHelper::$wallet_operation,
    //                         $reason,
    //                         $amount,
    //                         ''
    //                     );
    //                 } elseif ($payment->gateway == 'tap') {
    //                     $result = $tap->createRefund(
    //                         $payment->operation_id,
    //                         $payment->order_id,
    //                         $amount,
    //                         'refund_order',
    //                         $payment->key_type
    //                     );
    //                 } elseif ($payment->gateway == 'moyasar') {
    //                     try {
    //                         $moyasar_payment = \Moyasar\Facades\Payment::fetch($payment->operation_id);
    //                         $moyasar_payment->refund((int)$amount);
    //                     } catch (\Exception $exception) {
    //                          LogsHelper::appError(LogsHelper::$moyasar_payment_error, [
    //                             'class' => 'PaymentsHelper',
    //                             'line_number' => 367,
    //                             'error' => $exception->getMessage()
    //                         ]);
    //                     }
    //                 }
    //             }
    //             /**
    //              * if there canceling fees added to tyqn plus account
    //              */
    //             if ($canceled_amount > 0) {
    //                 // if there fees withdraw it from client
    //                 if ($payment->gateway == 'wallet') {
    //                     WalletHelper::addWithdrawOperation(
    //                         $order->created_by,
    //                         $order->id,
    //                         PaymentsHelper::$wallet_operation,
    //                         PaymentsHelper::$canceling_order_reason,
    //                         $canceled_amount,
    //                         ''
    //                     );
    //                 }

    //                 if ($payment->gateway == 'wallet') {
    //                     WalletHelper::deposit($tyqn_plus_user->id, $canceled_amount, '');
    //                 }
    //                 PaymentsOperations::create([
    //                     'user_id' => $tyqn_plus_user->id,
    //                     'created_by' => auth()->check() ? auth()->user()->id : $tyqn_plus_user->id,
    //                     'order_id' => $payment->order_id,
    //                     'operation' => $payment->operation,
    //                     'operation_id' => $payment->operation_id,
    //                     'type' => PaymentsHelper::$deposit_type,
    //                     'reason' => PaymentsHelper::$canceling_order_reason,
    //                     'amount' => $canceled_amount,
    //                     'reference' => $payment->reference,
    //                     'details' => $payment->details,
    //                     'gateway' => $payment->gateway,
    //                     'status' => $payment->status,
    //                 ]);
    //             }

    //             /**
    //              * register payment operation as refunded
    //              */
    //             $payment->status = self::$payment_refunded_status;
    //             $payment->save();
    //         }
    //     }

    //     /**
    //      * add client gift amount if not previous gifted
    //      */
    //     $has_pref_gift_amount = PaymentsOperations::where('user_id', $order->created_by)
    //         ->where('order_id', $order->id)
    //         ->where('type', PaymentsHelper::$manager_operation)
    //         ->where('reason', PaymentsHelper::$wallet_gift_reason)
    //         ->first();

    //     if (!$order->company_id && $order->offer && !$has_pref_gift_amount && $canceled_by != 'client') {
    //         $client_gift_amount = getSetting('client_gift_amount', 0);
    //         WalletHelper::deposit($order->owner->id, $client_gift_amount, 'Gift amount');
    //         WalletHelper::addDepositOperation(
    //             $order->owner->id,
    //             $order->id,
    //             PaymentsHelper::$manager_operation,
    //             PaymentsHelper::$wallet_gift_reason,
    //             $client_gift_amount,
    //             'Gift amount'
    //         );
    //     }
    // }
}
