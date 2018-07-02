<?php 

require_once '../vendor/autoload.php';

use Payment\Payment;
use Payment\Moip;
use Payment\PagarMe;

$config = ['access_token' => '6b3f198d1d994d7da8ef482f70368963_v2'];
$payment = new Payment(new Moip($config));


$config = ['access_token' => 'ak_test_oVkp8o1tBmYJR3GtbQ9L0Nkx8ygHM7'];
$payment = new Payment(new PagarMe($config));
$payment->create();
$phone = $payment->addPhone([]);
pr($phone); exit;
$payment->addCustomer([]);
$response = $payment->send();

pr($response); exit;


