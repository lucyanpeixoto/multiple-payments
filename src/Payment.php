<?php

namespace Payment;

use Payment\Exceptions\ValidationException;

class Payment {

    const PRIMARY_RECEIVER = 1;
    const SECONDARY_RECEIVER = 2;
    const CREDIT_CARD = 1;
    const BOLETO = 2;
    const ONLINE_DEBIT = 3;

    private $intermediary;

    public function __construct(Intermediary $intermediary) 
    {
        return $this->intermediary = $intermediary;
    }

    public function create() 
    {
        return $this->intermediary->create();
    }

    /**
     * return @mixed
     */
    
    public function send() 
    {
        return $this->intermediary->send();
    }

    /**
     * @param $items
     * return @mixed
     */

    public function addItems($items) 
    {
        if (!isset($items)) {
            throw new ValidationException('$items é obrigatório', 400);
        }

        return $this->intermediary->addItems($items);
    }

    /**
     * @param $name
     * @param $price
     * @param int $quantity
     * @param string $description
     * return @mixed
     */

    public function addItem($name, $price, $quantity = 1, $description = '') 
    {
        return $this->intermediary->addItem($name, $price, $quantity, $description);
    }

    /**
     * @param $data
     * @throws ValidationException
     * @throws ValidationException
     */

    public function addReceiver($receiver) 
    {
        if (!isset($receiver)) {
            throw new ValidationException('$receiver é obrigatório', 400);
        }elseif (!is_array($receiver)) {
            throw new ValidationException('$data deve ser array, foi passado '.gettype($receiver), 400);
        }elseif (!isset($receiver['receiverId']) || empty($receiver['receiverId'])) {
            throw new ValidationException('receiver_id é obrigatório', 400);
        }elseif (isset($receiver['type']) && !in_array($receiver['type'], [self::PRIMARY_RECEIVER, self::SECONDARY_RECEIVER])) {
            throw new ValidationException('$data[\'type\'] tem que ser 1 (Primário) ou 2 (Secundário), foi passado ' . $receiver['type'], 400);
        }

        $receiver += [
            'type' => self::PRIMARY_RECEIVER,
            'fixed' => null,
            'percentage' => null,
            'feePayor' => false
        ];

        return $this->intermediary->addReceiver($receiver);
    }

    /**
     * @param $data
     * return @mixed
     */

    public function addCustomer($customer) 
    {
        if (!isset($customer)) {
            throw new ValidationException('$customer é obrigatório', 400);
        }

        return $this->intermediary->addCustomer($customer);
    }

    /**
     * @param $uniqueId
     * return @mixed
     */

    public function addUniqueId($uniqueId) 
    {
        if (!isset($uniqueId)) {
            throw new ValidationException('$uniqueId é obrigatório', 400);
        }

        return $this->intermediary->addUniqueId($uniqueId);
    }

    /**
     * @param $notificationUrl
     * return @mixed
     */

    public function addNotificationUrl($notificationUrl) 
    {
        if (!isset($notificationUrl)) {
            throw new ValidationException('$uniqueId é obrigatório', 400);
        }

        return $this->intermediary->addNotificationUrl($notificationUrl);
    }

    /**
     * @param $type
     * @param $data
     * return @mixed
     */

    public function addPaymentMethod($type, $data) 
    {
        if (!isset($type)) {
            throw new ValidationException('$type é obrigatório', 400);
        }elseif (!isset($data)){
            throw new ValidationException('$data é obrigatório', 400);
        }

        return $this->intermediary->addPaymentMethod($type, $data);
    }
}