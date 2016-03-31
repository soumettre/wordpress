<?php

class SoumettreApiClient {

//    private $endpoint = 'http://10.0.2.15:8001/api/';
    private $endpoint = 'https://soumettre.fr/api/';

    function __construct($prefix = 'soum_sour_') {
        $this->prefix = $prefix;

        $this->email = get_option($this->prefix.'email');
        $this->api_key = get_option($this->prefix.'api_key');
        $this->api_secret = get_option($this->prefix.'api_secret');
    }

    public function test() {
        $res = $this->request('test', null);

        echo $res['data'];
        die();
    }

    public function site_add() {
        $res = $this->request('site/register', array('site' => get_home_url()));
        echo $res['data'];
        die();
    }

    protected function request($endpoint, $params) {
        $endpoint = $this->endpoint . $endpoint;

        $params = $this->sign($params);

        $res = wp_remote_post( $endpoint, array('body' => $params));

        if (isset($res['body'])) {
            return array('response' => $res, 'data' => $res['body']);
        }

        return json_encode($params);
    }

    protected function sign($params) {

        $time = time();
        $signature = md5($this->api_key.'-'.$this->api_secret.' '.$time);

        $params['user'] = $this->email;
        $params['key'] = $this->api_key;
        $params['time'] = $time;
        $params['sign'] = $signature;

        return $params;
    }

}