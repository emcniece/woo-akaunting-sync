<?php

class Requester{
    public $user;
    public $pass;

    public function __construct($user, $pass){
        $this->user = $user;
        $this->pass = $pass;
    }

    public function get($url){
        return $this->request($url, 'GET');
    }

    public function post($url, $data){
        return $this->request($url, 'POST', array('body' => $data));
    }

    public function patch($url, $data){
        return $this->request($url, 'PATCH', array('body' => $data));
    }

    private function request($url, $method, $args=array()){
        $encoded = base64_encode($this->user.':'.$this->pass);
        $args['timeout'] = 25;
        $args['method'] = $method;
        $args['headers'] = array('Authorization' => "Basic $encoded");

        $response = wp_remote_request($url, $args);
        $content = wp_remote_retrieve_headers($response);

        if (!is_array($content)) {
            $content = $content->getAll();
        } else {
            $content = wp_remote_retrieve_body($response);
        }

        return $content;
    }
}