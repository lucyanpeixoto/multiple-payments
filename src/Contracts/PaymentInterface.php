<?php

namespace Payment\Contracts;


interface PaymentInterface {

    /**
     * @param empty
     */
    public function create();

    /**
     * @param string $uniqueId
     */
    function addUniqueId($uniqueId);

    /**
     * @param $items
     */
    function addItems($items);

    /**
     * @param $data
     */
    function addCustomer($data);

    /**
     * @param string $name
     * @param float $price
     * @param int $quantity
     * @param string $description
     */
    function addItem($name, $price, $quantity = 1, $description = '');

    /**
     * @param string $type
     * @param $data
     */
    function addPaymentMethod($type, $data);

    /**
     * @param array $data = ['receiver_id', 'type' => 'PRIMARY', 'fixed' => null, 'percentual' => null, 'feePayor' => false']
     */
    function addReceiver($data);

    /**
     * @param empty
     */
    function send();
}