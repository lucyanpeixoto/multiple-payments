<?php

namespace Payment;

use PagarMe\Sdk\Customer\Phone;
use PagarMe\Sdk\Customer\Address;
use Payment\Contracts\PaymentInterface;
use PagarMe\Sdk\PagarMe as PagarMeSdk;
use PagarMe\Sdk\ClientException;
use Payment\Exceptions\ValidationException;

class PagarMe extends Intermediary implements PaymentInterface
{

    private $data;
    private $pagarMe;
    private $payment;
    private $customer;

    public function __construct($config = []) 
    {
        $this->setDefaults($config);

        if (!empty($config) && is_string($config)) {
            $this->pagarMe = new PagarMeSdk($data);
        }else if (isset($config['access_token'])) {
            $this->pagarMe = new PagarMeSdk($config['access_token']);
        }
    }

    public function setDefaults($config) 
    {
        $config = array_merge($config, ['env' => 'sandbox']);
        $this->setEnv($config['env']);
    }

    public function create() 
    {
        return $this->payment;
    }

    public function auth()
    {
        
    }

    public function getAuth()
    {
        return $this->pagarMe;
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
        $this->payment = $this->pagarMe->transaction()->boletoTransaction(
            1000,
            $this->customer,
            'http://requestb.in/pkt7pgpk',
            ['idProduto' => 13933139]
        );
        
    }

    public function addCustomer($data) 
    {
        try {
            $this->customer = $this->pagarMe->customer()->create(
                $data['name'] . ' ' . $data['lastName'],
                $data['email'],
                $data['taxDocument'],
                $this->addAddress($data),
                $this->addPhone($data)
            );

        }catch (ClientException $e) {
            throw new ValidationException($e->getMessage());
        }
        
        pr($this->customer); exit;
    }

    public function addPhone($data) 
    {
        return new Phone([
            'ddd' => substr($data['phone'], 0, 2),
            'number' => substr($data['phone'], 2, 9),
            'ddi' => '55'
        ]);
    }

    private function addAddress($data) 
    {        
        $street = $data['street'];
        $streetNumber = $data['number'];
        $neighborhood = $data['district'];
        $zipcode = $data['street'];
        $complementary = $data['complement'];
        $city = $data['city'];
        $state = $data['state'];
        $country = $data['country'];

        return new Address([
            'street' => $street,
            'streetNumber' => $streetNumber,
            'neighborhood' => $neighborhood,
            'zipcode' => $zipcode,
            'complementary' => $complementary,
            'city' => $city,
            'state' => $state,
            'country' => $country
        ]);
    }

}