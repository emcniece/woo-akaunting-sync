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
        return $this->request($url, array('body' => $data), 'POST');
    }

    public function patch($url, $data){
        return $this->request($url, array('body' => $data), 'PATCH');
    }

    private function request($url, $args = array(), $method){
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