<?php

namespace Payment;

class Intermediary {

    private $accessToken;
    private $token;
    private $key;
    private $endPoint;
    private $notificationUrl;

    const BOLETO = 2;
    const DEBITO = 3;
    const CARTAO_DE_CREDITO = 1;
    const PRIMARY_RECEIVER = 1;
    const SECONDARY_RECEIVER = 2;


    public function setAccessToken($accessToken) {
        $this->accessToken = $accessToken;
    }

    public function getAccessToken() {
        return $this->accessToken;
    }

    public function setEndPoint($endPoint) {
        $this->endPoint = $endPoint;
    }

    public function getEndPoint() {
        return $this->endPoint;
    }

    public function setNotificationUrl($notificationUrl) {
        $this->notificationUrl = $notificationUrl;
    }

    public function getNotificationUrl() {
        return $this->notificationUrl;
    }

    public function getToken(){
        return $this->token;
    }

    public function setToken($token){
        $this->token = $token;
    }

    public function getKey(){
        return $this->key;
    }

    public function setKey($key){
        $this->key = $key;
    }


}