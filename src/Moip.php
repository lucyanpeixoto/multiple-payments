<?php
namespace Payment;

use Moip\Auth\BasicAuth;
use Moip\Auth\Connect;
use Moip\Auth\OAuth;
use Moip\Exceptions\UnautorizedException;
use Moip\Exceptions\UnexpectedException;
use Moip\Exceptions\ValidationException as MoipValidationException;
use Moip\Moip as MoipSdk;
use Payment\Contracts\PaymentInterface;
use Payment\Exceptions\ValidationException;

class Moip extends Intermediary implements PaymentInterface{

    private $moip;
    private $order;
    private $accessToken;
    private $items;
    private $receiver;
    private $customer;
    private $uniqueId;
    private $paymentMethodData;
    private $paymentMethodType;
    private $payment;

    public function __construct($config = []) 
    {
        $this->setDefaults($config);

        if (isset($config['token']) && !empty($config['token']) && isset($config['key']) && !empty($config['key'])) {               
            $this->setToken($config['token']);
            $this->setKey($config['key']);
            $this->moip = $this->basicAuth();
        }elseif (isset($config['access_token']) && !empty($config['access_token'])) {
            $this->setAccessToken($config['access_token']);
            $this->moip = $this->auth();
        }        
    }

    public function create() {
        $this->order = $this->moip->orders();
    }

    public function setDefaults($config) 
    {
        $config = array_merge($config, ['env' => 'sandbox']);

        $this->setEnv($config['env']);

        if ($this->getEnv() == self::PRODUCTION) {
            $this->setEndPoint(MoipSdk::ENDPOINT_PRODUCTION);
        }else {
            $this->setEndPoint(MoipSdk::ENDPOINT_SANDBOX);
        }
    }

    public function basicAuth() 
    {
        return new MoipSdk(new BasicAuth($this->getToken(), $this->getKey()), $this->getEndPoint());
    }

    public function auth() 
    {
        return new MoipSdk(new OAuth($this->getAccessToken()), $this->getEndPoint());
    }

    public function createApp($data) 
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode($this->getToken() . ':' . $this->getKey())
            )
        );
        curl_setopt($ch, CURLOPT_URL, $this->getEndPoint() . "/v2/channels");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec ($ch);

        $app = json_decode($server_output);
        curl_close ($ch);

        return $app;
    }

    public function createAccount($data) 
    {
        $defaults = [
            'type' => 'MERCHANT',
            'country_code' => 55,
            'country' => 'BRA'
        ];

        $data = array_merge($data, $defaults);

        try {
            return $this->moip->accounts()
                ->setName($data['name'])
                ->setLastName($data['lastName'])
                ->setEmail($data['email'])
                ->setBirthDate($data['birthDate'])
                ->setTaxDocument($data['taxDocument'])
                ->setType($data['type'])
                ->setPhone(substr($data['phone'], 0, 2), substr($data['phone'], 2, 9), $data['country_code'])
                ->addAddress($data['street'], $data['number'],
                    $data['district'], $data['city'], $data['state'],
                    $data['zip'], $data['complement'], $data['country'])
                ->create(); 
        }catch (UnexpectedException $e) {
            throw new ValidationException($e->getMessage(), 400);
        }catch (MoipValidationException $e) {
            throw new ValidationException($e->__toString(), 400);
        }catch (UnautorizedException $e) {
            throw new ValidationException($e->getMessage(), 403);
        }
    }


    public function getAuthUrl($data = []) 
    {
        $connect = new Connect($data['redirectUri'], $data['appId'], true, $this->getEnv() == 'production' ? Connect::ENDPOINT_PRODUCTION : Connect::ENDPOINT_SANDBOX);
        $connect->setScope(Connect::RECEIVE_FUNDS)
            ->setScope(Connect::REFUND)
            ->setScope(Connect::MANAGE_ACCOUNT_INFO)
            ->setScope(Connect::RETRIEVE_FINANCIAL_INFO);
        return $connect->getAuthUrl();
    }

    public function getOAuth($data = []) 
    {
        try {
            $connect = new Connect($data['redirectUri'], $data['appId'], true,  $this->getEnv() == 'production' ? Connect::ENDPOINT_PRODUCTION : Connect::ENDPOINT_SANDBOX);
            $connect->setClientSecret($data['secret']);
            $connect->setCode($data['code']);
            return $connect->authorize();
        }catch (UnexpectedException $e) {
            throw new ValidationException($e->getMessage(), 400);
        }catch (MoipValidationException $e) {
            throw new ValidationException($e->__toString(), 400);
        }catch (UnautorizedException $e) {
            throw new ValidationException($e->getMessage(), 403);
        }
    }


    public function send() 
    {
        try {
            //Criando um pedido
            $this->order = $this->order->create();
            //Tratando forma de pagamento
            $this->setPaymentMethod();
            //Criando um pagamento
            return $this->payment->execute();

        }catch (UnexpectedException $e) {
            throw new ValidationException($e->getMessage(), 400);
        }catch (MoipValidationException $e) {
            throw new ValidationException($e->__toString(), 400);
        }catch (UnautorizedException $e) {
            throw new ValidationException($e->getMessage(), 403);
        }
    }

    public function addItems($items) 
    {
        foreach($items as $key => $item) {
            //$item = new Item($item['name'], $item['price'], $item['quantity'], $item['description']);
            $this->order->addItem($item['name'], $item['quantity'], $item['description'], $item['price']);
        }
    }

    public function addItem($name, $price, $quantity = 1, $description = '') 
    {
        //$item = new Item($name, $price, $quantity, $description);
        $this->order->addItem($name, $quantity, $description, $price);
    }

    public function addReceiver($data) 
    {
        $data['type'] = $data['type'] == self::PRIMARY_RECEIVER ? 'PRIMARY' : 'SECONDARY';

        $this->order->addReceiver($data['receiverId'], $data['type'], $data['fixed'], $data['percentage'], $data['processingFee']);
    }

    public function addCustomer($data) 
    {
        $defaults = ['complement' => ''];

        $data = array_merge($data, $defaults);

        try {
            $customer = $this->moip->customers()->setOwnId(uniqid())
                ->setFullname($data['name'] . ' ' . $data['lastName'])
                ->setEmail($data['email'])
                ->setTaxDocument($data['taxDocument'])
                ->setPhone(substr($data['phone'], 0, 2), substr($data['phone'], 2, 9))
                ->addAddress('BILLING',
                    $data['street'], $data['number'],
                    $data['district'], $data['city'], $data['state'],
                    $data['zip'], $data['complement'], $data['country'])
                ->addAddress('SHIPPING',
                    $data['street'], $data['number'],
                    $data['district'], $data['city'], $data['state'],
                    $data['zip'], $data['complement'], $data['country'])
                ->create();    
                
            $this->order->setCustomer($customer );     

        }catch (UnexpectedException $e) {
            throw new ValidationException($e->getMessage(), 400);
        }catch (MoipValidationException $e) {
            throw new ValidationException($e->__toString(), 400);
        }catch (UnautorizedException $e) {
            throw new ValidationException($e->getMessage(), 403);
        }
    }

    public function addUniqueId($uniqueId) 
    {
        $this->order->setOwnId($uniqueId);
    }

    public function setPaymentMethod() 
    {
        if ($this->paymentMethodType == self::BOLETO) {
            $logo_uri = 'https://cdn.moip.com.br/wp-content/uploads/2016/05/02163352/logo-moip.png';
            $expiration_date = new \DateTime();
            $instruction_lines = ['INSTRUÇÃO 1', 'INSTRUÇÃO 2', 'INSTRUÇÃO 3'];

            $this->payment = $this->order->payments()
                ->setBoleto($expiration_date, $logo_uri, $instruction_lines);
        }
    }

    public function addPaymentMethod($type, $data) 
    {
        $this->paymentMethodType = $type;
        $this->paymentMethodData = $data;
    }

    public function getAppAccount() 
    {        
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode($this->getToken() . ':' . $this->getKey())
            )
        );

        curl_setopt($ch, CURLOPT_URL, $this->getEndPoint() . "/v2/accounts");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $account = json_decode(curl_exec($ch));

        curl_close ($ch);

        return $account;
    }

    public function checkAccountExists($taxDocument)
    {
        if ($taxDocument == null) {
            throw new ValidationException('taxDocument é obrigatório', 400);
        }

        try{ 
            return $this->moip->accounts()->checkExistence($taxDocument); 
        }catch (UnexpectedException $e) {
            throw new ValidationException($e->getMessage(), 400);
        }catch (MoipValidationException $e) {
            throw new ValidationException($e->__toString(), 400);
        }catch (UnautorizedException $e) {
            throw new ValidationException($e->getMessage(), 403);
        } 
    }

    public function consultAccount($clientId)
    {
        if ($clientId == null) {
            throw new ValidationException('clientId é obrigatório', 400);
        }

        try{
            $account = $this->moip->accounts()->get($clientId);
            return $account;
        }catch (UnexpectedException $e) {
            throw new ValidationException($e->getMessage(), 400);
        }catch (MoipValidationException $e) {
            throw new ValidationException($e->__toString(), 400);
        }catch (UnautorizedException $e) {
            throw new ValidationException($e->getMessage(), 403);
        } 
    }
}