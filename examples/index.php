<?php

require_once '../vendor/autoload.php';

use Payment\Payment;
use Payment\Moip;
use Payment\Exceptions\InvalidArgumentException;
use Payment\Exceptions\RequiredArgumentException;

$accessToken = '5f69ffd5b6a84209af6b971d013e60c8_v2';
$accessTokenApp = '6de742a5ec434703aab60a648305290b_v2';
$token = 'IVYBKAKRP8HN9MSUJOPBP7QESIANKECF';
$key = 'LTGUDRGBTJ8SSQUN7XMA0TVXMIUIJKM7OZQDSD2H';

$uniqueId = '93120901';
$items = json_decode(file_get_contents('data/items.json'), true);
$receiver = json_decode(file_get_contents('data/receiver.json'), true);
$customer = json_decode(file_get_contents('data/customer.json'), true);
$paymentData = json_decode(file_get_contents('data/payment-data.json'), true);
$appData = json_decode(file_get_contents('data/app-data.json'), true);
$type = 2;

try {

    //$moip = new Moip($token, $key);
    //$app = $moip->createApp($appData);


    $payment = new Payment(new Moip($accessTokenApp));
    $payment->addUniqueId($uniqueId);
    $payment->addCustomer($customer);
    $payment->addItems($items);
    $payment->addItem('item teste', 200, 5);
    $payment->addPaymentMethod($type, $paymentData);
    $payment->addReceiver($receiver);
    $response = $payment->send();

    pr($response);
    exit;
}catch (InvalidArgumentException $e) {
    pr($e->getCode());
    pr($e->getMessage()); exit;
}catch (RequiredArgumentException $e) {
    pr($e->getCode());
    pr($e->getMessage()); exit;
}