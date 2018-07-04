<?php

namespace Payment;

use PagarMe\Sdk\Customer\Phone;
use PagarMe\Sdk\Customer\Address;
use PagarMe\Sdk\Customer\Customer;
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
    private $uniqueId;
    private $items;
    private $receiver;

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
        $this->uniqueId = $uniqueId;
    }


    public function addItems($items)
    {
        $this->items = $items;
    }


    public function addItem($name, $price, $quantity = 1, $description = '')
    {
        $item = [
            'name' => $name,
            'price' => $price,
            'quantity' => $quantity,
            'description' => $description,
        ];

        $this->items[] = $item;
    }


    public function addPaymentMethod($type, $data) 
    {

    }

    public function addReceiver($receiver) 
    {
        $transferInterval = "monthly";
        $transferDay = 13;
        $transferEnabled = true;
        $automaticAnticipationEnabled = true;
        $anticipatableVolumePercentage = 42;
        $this->receiver[] = $this->pagarMe->recipient()->create(
            $receiver,
            $transferInterval,
            $transferDay,
            $transferEnabled,
            $automaticAnticipationEnabled,
            $anticipatableVolumePercentage
        );
    }


    public function send() 
    {
        return $this->pagarMe->transaction()->boletoTransaction(
            1000,
            $this->customer,
            'http://requestb.in/pkt7pgpk',
            [
                'uniqueId' => $this->uniqueId,
                'items' => $this->items
            ]
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

    public function createReceiver($data) 
    {
        try {
            $bankAccount = $this->pagarMe->bankAccount()->create(
                $data['bankCode'],
                $data['agencyNumber'],
                $data['accountNumber'],
                $data['accountDigit'],
                $data['taxDocument'],
                $data['holder'],
                $data['agencyDigit']
            );
            return $bankAccount;
        } catch (ClientException $e) {
            throw new ValidationException($e->getMessage());
        }
    }
}