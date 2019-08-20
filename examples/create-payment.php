<?php

require_once '../vendor/autoload.php';

use Payment\Payment;
use Payment\Moip;
use Payment\PagarMe;
use Payment\Customer;
use Payment\Exceptions\ValidationException;

//Seller
$MoipAccessToken = '2f35e3dad14b46718e15028ae833eeeb_v2';
$PagarMeAccessToken = 'ak_test_oVkp8o1tBmYJR3GtbQ9L0Nkx8ygHM7';

$uniqueId = '93120901';
$items = json_decode(file_get_contents('data/items.json'), true);
$receiver = json_decode(file_get_contents('data/receiver.json'), true);
$customer = json_decode(file_get_contents('data/customer.json'), true);
$paymentData = json_decode(file_get_contents('data/payment-data.json'), true);
try {
    $payment = new Payment(new Moip(['access_token' => $MoipAccessToken]));
    
    $customer = new Customer();
    $customer->setLastName('haha');
    $customer->setTax_document('haha');
    

    pr($customer); exit;

    $payment->create();
    $payment->addCustomer($customer);
    $payment->addUniqueId($uniqueId);
    $payment->addItems($items);
    $payment->addItem('item teste', 200, 5);
    $payment->addPaymentMethod(Payment::BOLETO, $paymentData);
    $payment->addReceiver($receiver);
    $response = $payment->send();

    pr($response); exit;

}catch (ValidationException $e) {
    pr(json_decode($e->getMessage())); exit;
}