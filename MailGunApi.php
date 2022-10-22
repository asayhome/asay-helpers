<?php


namespace App\Plugins\Helpers;


class MailGunApi
{

    private $api_key;
    private $domain;

    public function __construct($api_key, $domain)
    {
        $this->api_key = $api_key;
        $this->domain = $domain;
    }


    public function updateTemplate($template_name, $template_content,$tag_version='initial', $options = [])
    {
        $params = [
            'active' => 'yes'
        ];
        if (isset($options['comment'])) {
            $params['comment'] = $options['comment'];
        }
        $params = array(
            'template' => $template_content,
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, 'api:'.$this->api_key);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_URL, 'https://api.mailgun.net/v3/'.$this->domain.'/templates/'.$template_name.'/versions/'.$tag_version);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

}
