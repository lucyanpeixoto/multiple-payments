<?php

namespace Payment;

use Payment\Contracts\PaymentInterface;
use PagarMe\Sdk\PagarMe as PagarMeSdk;

class PagarMe extends Intermediary implements PaymentInterface
{

    private $data;
    private $pagarme;

    public function __construct($data = []) 
    {
        if (!empty($data) && is_string($data)) {
            $this->pagarme = new PagarMeSdk($data);
        }else if (isset($data['access_token'])) {
            $this->pagarme = new PagarMeSdk($data['access_token']);
        }
    }

    public function auth()
    {
    }

    public function getAuth()
    {
        return $this->pagarme;
    }


    public function addUniqueId($uniqueId)
    {

    }


    public function addItems($items)
    {

    }


    public function addItem($name, $price, $quantity = 1, $description = '')
    {

    }


    public function addPaymentMethod($type, $data) 
    {

    }

    public function addReceiver($data) 
    {

    }


    public function send() 
    {

    }

}