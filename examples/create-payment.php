<?php

require_once '../vendor/autoload.php';

use Payment\Payment;
use Payment\Moip;
use Payment\Exceptions\InvalidArgumentException;
use Payment\Exceptions\RequiredArgumentException;

//Seller
$accessToken = '2f35e3dad14b46718e15028ae833eeeb_v2';

$uniqueId = '93120901';
$items = json_decode(file_get_contents('data/items.json'), true);
$receiver = json_decode(file_get_contents('data/receiver.json'), true);
$customer = json_decode(file_get_contents('data/customer.json'), true);
$paymentData = json_decode(file_get_contents('data/payment-data.json'), true);

try {
    $payment = new Payment(new Moip(['access_token' => $accessToken]));
    
    $payment->create();
    $payment->addUniqueId($uniqueId);
    $payment->addCustomer($customer);
    $payment->addItems($items);
    $payment->addItem('item teste', 200, 5);
    $payment->addPaymentMethod(Payment::BOLETO, $paymentData);
    $payment->addReceiver($receiver);
    $response = $payment->send();

    pr($response); exit;

}catch (InvalidArgumentException $e) {
    pr($e->getCode());
    pr($e->getMessage()); exit;
}catch (RequiredArgumentException $e) {
    pr($e->getCode());
    pr($e->getMessage()); exit;
}