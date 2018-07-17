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
            'country' => 'BRA',
            'complement' => '',
            'companyComplement' => ''
        ];

        $data = array_merge($data, $defaults);

        try {
            $account = $this->moip->accounts()
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

                if ( (isset($data['companyTaxDocument'])) && (!empty($data['companyTaxDocument']))) {
                    $account->setCompanyName($data['companyName'], $data['businessName'])
                        ->setCompanyOpeningDate($data['openingDate'])
                        ->setCompanyPhone(substr($data['companyPhone'], 0, 2), substr($data['companyPhone'], 2, 9), $data['country_code'])
                        ->setCompanyTaxDocument($data['companyTaxDocument'])
                        ->setCompanyAddress($data['companyStreet'], $data['companyNumber'], $data['companyDistrict'], $data['companyCity'], $data['companyState'], $data['companyZip'], $data['companyComplement'], $data['country']);
                }
                return $account;

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

    public function addNotificationUrl($notificationUrl) 
    {
        try {
            $this->moip->notifications()
                ->addEvent('ORDER.*')
                ->addEvent('PAYMENT.*')
                ->setTarget($notificationUrl)
                ->create();
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
            $this->order->addItem($item['name'], $item['quantity'], $item['description'], intval($item['price']));
        }
    }

    public function addItem($name, $price, $quantity = 1, $description = '') 
    {
        $this->order->addItem($name, $quantity, $description, $price);
    }

    public function addReceiver($data) 
    {
        $data['type'] = $data['type'] == self::PRIMARY_RECEIVER ? 'PRIMARY' : 'SECONDARY';

        $this->order->addReceiver($data['receiverId'], $data['type'], $data['fixed'], $data['percentage'], $data['feePayor']);
    }

    public function addCustomer($data) 
    {
        $defaults = [
            'id' => uniqid(),
        ];
        
        $data = array_merge($data, $defaults);        
        $data['billingAddress']['complement'] = isset($data['billingAddress']['complement']) ? $data['billingAddress']['complement'] : '';
        $data['billingAddress']['country'] = isset($data['billingAddress']['country']) ? $data['billingAddress']['country'] : 'BRA';

        try {
            $customer = $this->moip->customers()->setOwnId($data['id'])
                ->setFullname($data['name'] . ' ' . $data['lastName'])
                ->setEmail($data['email'])
                ->setTaxDocument($data['taxDocument'])
                ->setPhone(substr($data['phone'], 0, 2), substr($data['phone'], 2, 9))
                ->addAddress('BILLING',
                    $data['billingAddress']['street'], $data['billingAddress']['number'],
                    $data['billingAddress']['district'], $data['billingAddress']['city'], $data['billingAddress']['state'],
                    $data['billingAddress']['zip'], $data['billingAddress']['complement'], $data['billingAddress']['country'])
                ->addAddress('SHIPPING',
                    $data['billingAddress']['street'], $data['billingAddress']['number'],
                    $data['billingAddress']['district'], $data['billingAddress']['city'], $data['billingAddress']['state'],
                    $data['billingAddress']['zip'], $data['billingAddress']['complement'], $data['billingAddress']['country'])
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

            $this->payment = $this->order->payments()
                ->setBoleto(
                $this->paymentMethodData['expirationDate'], 
                $this->paymentMethodData['logoUri'], 
                $this->paymentMethodData['instructions']);
                
        }elseif ($this->paymentMethodType == self::ONLINE_DEBIT) {

            $this->payment = $this->order->payments()                    
                ->setOnlineBankDebit(
                    $this->paymentMethodData['bankNumber'], 
                    $this->paymentMethodData['expirationDate'],
                    $this->paymentMethodData['returnUri']);

        }elseif ($this->paymentMethodType == self::CREDIT_CARD) {

            if (isset($this->paymentMethodData['hash']) && !empty($this->paymentMethodData['hash'])) {
                $this->payment = $this->order->payments()
                    ->setCreditCardHash(
                        $this->paymentMethodData['hash'],
                        $this->setHolder($this->paymentMethodData['holder']))
                    ->setInstallmentCount($this->paymentMethodData['installments'])
                    ->setStatementDescriptor($this->paymentMethodData['statementDescription']);
            }else {
                $this->payment = $this->order->payments()                    
                    ->setCreditCard(
                        $this->paymentMethodData['expirationMonth'],
                        $this->paymentMethodData['expirationYear'],
                        $this->paymentMethodData['number'],
                        $this->paymentMethodData['cvc'],
                        $this->setHolder($this->paymentMethodData['holder']))
                    ->setInstallmentCount($this->paymentMethodData['installments'])
                    ->setStatementDescriptor($this->paymentMethodData['statementDescription']);
            }
        }
    }

    private function setHolder($data) 
    {         
        try {
            return $this->moip->holders()
                ->setFullname($data['name'] . ' ' . $data['lastName'])
                ->setBirthDate($data['birthDate'])
                ->setTaxDocument($data['taxDocument'])
                ->setPhone(substr($data['phone'], 0, 2), substr($data['phone'], 2, 9));           

        }catch (UnexpectedException $e) {
            throw new ValidationException($e->getMessage(), 400);
        }catch (MoipValidationException $e) {
            throw new ValidationException($e->__toString(), 400);
        }catch (UnautorizedException $e) {
            throw new ValidationException($e->getMessage(), 403);
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

    public function sdkCheckAccountExists($taxDocument)
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

    public function checkAccountExists($data)
    {        

        if ($data == null) {
            throw new ValidationException('$data é obrigatório', 400);
        }

        $param = key($data);
        $value = $data[$param];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->getAccessToken()
            )
        );

        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $this->getEndPoint() . "/v2/accounts/exists?$param=$value"
        ));        
        
        $response = curl_exec($ch);
        curl_close ($ch);

        return $response;
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

    public function refreshAccessToken($refreshToken)
    {        
        $data = [
            "grant_type" => "refresh_token",
            "refresh_token" => $refreshToken,
        ];        
        
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://connect-sandbox.moip.com.br/oauth/token");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $server_output = curl_exec ($ch);
        $response = json_decode($server_output);
        curl_close ($ch);
        
        return $response;
    }

    public function getPublicKey()
    {
        try{            
            return $this->moip->keys()->get();
        }catch (UnexpectedException $e) {
            throw new ValidationException($e->getMessage(), 400);
        }catch (MoipValidationException $e) {
            throw new ValidationException($e->__toString(), 400);
        }catch (UnautorizedException $e) {
            throw new ValidationException($e->getMessage(), 403);
        }
    }
}