<?php

namespace AsayHome\AsayHelpers\Helpers;


use App\Models\Auth\User;

class OneSignalHelper
{

    const create_segment_url = 'https://onesignal.com/api/v1/notifications';


    private static function getCredential($user_id)
    {
        $setting = getSetting([
            'onesignal_client_app_id',
            'onesignal_client_app_key'
        ]);
        return [
            'app_id' => $setting['onesignal_client_app_id'],
            'app_key' => $setting['onesignal_client_app_key'],
            'user_auth_key' => ''
        ];
    }


    public static function getUserAppId($user_id)
    {
        return getSetting('onesignal_client_app_id', '');
    }


    public static function sendNotificationToUser($user_id, $title, $message, $data = [], $url = null)
    {
        return self::sentNotify($user_id, $title, $message, $data, $url);
    }

    private static function sentNotify($id, $title, $message, $data = array('key' => 'value'), $url = '')
    {
        $data['timestamp'] = date("Y-m-d H:i:s");

        $user_id = $id;
        $user = User::where('id', $id)->first();
        if ($user) {
            $user_id = $user->last_login_id;
        }

        if (strlen($url) > 0) {
            $data['url'] = $url;
        } else {
            $data['url'] = 'https://tyqnn.com';
        }

        $fields = array(
            'include_external_user_ids' => [$user_id],
            'headings' => array(
                'en' => $title,
                'ar' => $title
            ),
            'contents' => array(
                'en' => $message,
                'ar' => $message
            ),
            'data' => $data
        );

        $credential = self::getCredential($id);
        $fields['app_id'] = $credential['app_id'];
        $result = self::doReguest(self::create_segment_url, $credential['app_key'], $fields);
        if (isset($result['errors'])) {
            unset($fields['app_id']);
            LogsHelper::appError('OneSignalApi', [
                'fields' => $fields,
                'result' => $result
            ]);
            return (object)array('success' => false, 'details' => $result);
        } else {
            return (object)array('success' => true, 'details' => $result);
        }
    }

    private static function doReguest($url, $app_secret, $fields)
    {
        $fields = json_encode($fields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic ' . $app_secret
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);;
    }
}
