<?php
class Dysmsapi {
    function __construct(){
        global $options;
        $key = trim(isset($options['aliyun_sms_keyid']) ? $options['aliyun_sms_keyid'] : '');
        $this->secret = trim(isset($options['aliyun_sms_secret']) ? $options['aliyun_sms_secret'] : '');
        $template = trim(isset($options['aliyun_sms_tcode']) ? $options['aliyun_sms_tcode'] : '');
        $sign = trim(isset($options['aliyun_sms_sign']) ? $options['aliyun_sms_sign'] : '');
        if($key && $this->secret && $template && $sign) {
            $this->config = array(
                'AccessKeyId' => $key,
                'Action' => 'SendSms',
                'Format' => 'JSON',
                'RegionId' => 'cn-hangzhou',
                'SignatureMethod' => 'HMAC-SHA1',
                'SignatureVersion' => '1.0',
                'Version' => '2017-05-25',
                'SignName' => $sign,
                'TemplateCode' => $template
            );
        }
    }
    function send($phone, $code, $nationCode='86'){
        if(isset($this->config)){
            $this->config['SignatureNonce'] = md5(time() . rand(100000, 999999));
            $this->config['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
            $this->config['PhoneNumbers'] = !$nationCode || $nationCode=='86' ? $phone : $nationCode.$phone;
            $this->config['TemplateParam'] = '{"code":"' . $code . '"}';
            $signature = $this->get_signature();
            $parameters = $this->config;
            $url = 'http://dysmsapi.aliyuncs.com/?Signature=' . $signature . '&' . http_build_query($parameters);
            $result = wp_remote_get($url, array('timeout' => 10, 'httpversion' => '1.1'));
            if(!is_wp_error($result)){
                $result = isset($result['body']) ? json_decode($result['body']) : '';
                if($result->Code==='OK'){
                    $result->result = 0;
                }else{
                    $result->result = -1;
                    $result->errmsg = $result->Message;
                }
            }else{
                $result = new stdClass();
                $result->result = -1;
                $result->errmsg = '发送失败';
            }
        }else{
            $result = new stdClass();
            $result->result = -1;
            $result->errmsg = '网站未配置短信接口';
        }
        return $result;
    }

    private function percent_encode($string){
        $result = urlencode($string);
        $result = str_replace(['+', '*'], ['%20', '%2A'], $result);
        $result = preg_replace('/%7E/', '~', $result);
        return $result;
    }

    private function rpc_string($method, $parameters){
        ksort($parameters);
        $canonicalized = '';
        foreach ($parameters as $key => $value) {
            $canonicalized .= '&' . $this->percent_encode($key) . '=' . $this->percent_encode($value);
        }
        return $method . '&%2F&' . $this->percent_encode(substr($canonicalized, 1));
    }
    private function get_signature(){
        $string = $this->rpc_string('GET', $this->config);
        return urlencode(base64_encode(hash_hmac('sha1', $string, $this->secret . '&', true)));
    }
}