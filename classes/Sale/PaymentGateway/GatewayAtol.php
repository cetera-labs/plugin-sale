<?php
namespace Sale\PaymentGateway;

abstract class GatewayAtol extends GatewayAbstract {

    const ATOL_PRODUCTION = 'https://online.atol.ru/possystem/v4/';
    const ATOL_TEST = 'https://testonline.atol.ru/possystem/v4/';    
	
    public static function getInfo2(){
        return [];
    }
    
	public static function getInfo() {
        
        $atolParams = [
            [
                "xtype"          => 'checkbox',
                "name"           => 'atol',
                "boxLabel"       => 'формировать кассовый чек',
                "inputValue"     => 1,
                "uncheckeDvalue" => 0
            ],
            [
                'name'       => 'atol_login',
                'xtype'      => 'textfield',
                'fieldLabel' => 'Логин',
                'allowBlank' => false,
            ],
            [
                'name'       => 'atol_pass',
                'xtype'      => 'textfield',
                'fieldLabel' => 'Пароль',
                'allowBlank' => false,
            ],                
            [
                'name'       => 'atol_group',
                'xtype'      => 'textfield',
                'fieldLabel' => 'Идентификатор группы ККТ',
                'allowBlank' => true,
            ],  
            [
                'name'       => 'atol_inn',
                'xtype'      => 'textfield',
                'fieldLabel' => 'ИНН',
                'allowBlank' => true,
            ],  
            [
                'name'       => 'atol_email',
                'xtype'      => 'textfield',
                'fieldLabel' => 'E-mail',
                'allowBlank' => true,
            ],  
            [
                'name'       => 'atol_payment_address',
                'xtype'      => 'textfield',
                'fieldLabel' => 'Адрес расчетов (URL)',
                'allowBlank' => true,
            ],                 
            [
                'name'       => 'atol_payment_object',
                'xtype'      => 'combobox',
                'fieldLabel' => 'Тип оплачиваемой позиции',
                'value'      => 1,
                'store'      => [
                    ["commodity", 'товар'],
                    ["excise", 'подакцизный товар'],
                    ["job", 'работа'],
                    ["service", 'услуга'],
                    ["payment", 'платёж'],
                    ["agent_commission", 'агентское вознаграждение'],
                ],
            ], 
            [
                'name'       => 'atol_payment_method',
                'xtype'      => 'combobox',
                'fieldLabel' => 'Тип оплаты',
                'value'      => 1,
                'store'      => [
                    ["full_prepayment", 'полная предварительная оплата до момента передачи предмета расчёта'],
                    ["prepayment", 'частичная предварительная оплата до момента передачи предмета расчёта'],
                    ["advance", 'аванс'],
                    ["full_payment", 'полная оплата в момент передачи предмета расчёта'],
                    ["partial_payment", 'частичная оплата предмета расчёта в момент его передачи с последующей оплатой в кредит'],
                    ["credit", 'передача предмета расчёта без его оплаты в момент его передачи с последующей оплатой в кредит'],
                    ["credit_payment", 'оплата предмета расчёта после его передачи с оплатой в кредит'],
                ],
            ],                 
            [
                'name'       => 'atol_sno',
                'xtype'      => 'combobox',
                'fieldLabel' => 'Система налогообложения',
                'value'      => 0,
                'store'      => [
                    ["osn", 'общая СН'],
                    ["usn_income", 'упрощенная СН (доходы)'],
                    ["usn_income_outcome", 'упрощенная СН (доходы минус расходы)'],
                    ["envd", 'единый налог на вмененный доход'],
                    ["esn", 'единый сельскохозяйственный налог'],
                    ["patent", 'патентная СН'],
                ],
            ],                 
            [
                'name'       => 'atol_vat',
                'xtype'      => 'combobox',
                'fieldLabel' => 'Ставка налога',
                'value'      => 0,
                'store'      => [
                    ["none", 'без НДС'],
                    ["vat0", 'НДС по ставке 0%'],
                    ["vat10", 'НДС чека по ставке 10%'],
                    ["vat18", 'НДС чека по ставке 18%'],
                    ["vat110", 'НДС чека по расчетной ставке 10/110'],
                    ["vat118", 'НДС чека по расчетной ставке 18/118'],
                    ["vat20", 'НДС чека по ставке 20%'],
                    ["vat120", 'НДС чека по расчётной ставке 20/120'],
                ],
            ],                       
        ];
        
        $data = static::getInfo2();
        
        if (isset($data['params'])){
            $params = $data['params'];
          } 
        
        //$data['params'] = array_merge($data['params'], $atolParams);
        
        $data['params'] = [
            'xtype' => 'tabpanel',
            'border' => false,
            'bodyCls' => 'x-window-body-default', 
            'items' => [
                [
                    "title" => 'Параметры',
                    'border' => false,
                    'bodyCls' => 'x-window-body-default',                     
                    "defaults" => [
                        'anchor' => '100%',
                        'labelWidth' => 200,
                    ],
                    "layout" => 'anchor',                    
                    'items' => $params,
                ],
                [
                    "title" => 'Кассовый чек ATOL',
                    'border' => false,
                    'bodyCls' => 'x-window-body-default',  
                    "defaults" => [
                        'anchor' => '100%',
                        'labelWidth' => 200,
                    ],
                    "layout" => 'anchor', 
                    'items' => $atolParams,
                ]
            ]
        ];
        
        return $data;
	}

    public static function sendQueue() {
        $data = self::getDbConnection()->fetchAll('SELECT id, order_id FROM sale_atol_queue WHERE is_sent=0 ORDER BY id');
        foreach ($data as $item) {
            $order = \Sale\Order::getById( $item['order_id'] );
            $order->getPaymentGateway()->sendFromQueue( $item['id'] );
        }
        $data = self::getDbConnection()->fetchAll('SELECT id, order_id FROM sale_atol_queue WHERE status="wait" ORDER BY id');
        foreach ($data as $item) {
            $order = \Sale\Order::getById( $item['order_id'] );
            $order->getPaymentGateway()->checkStatusFromQueue( $item['id'] );
        }		
    }
	
    private function getUrl() {
        return isset($this->params["test_mode"])?self::ATOL_TEST:self::ATOL_PRODUCTION;
    }
    
    private function auth() {

        if (isset($this->params["test_mode"])) {
            $this->params["atol_login"] = 'v4-online-atol-ru';
            $this->params["atol_pass"] = 'iGFFuihss';
        }

        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->request('POST', $this->getUrl().'getToken', [
                'verify' => false,
                'json' => [
                    'login' => $this->params["atol_login"],
                    'pass' => $this->params["atol_pass"],
                ],
            ]); 
        } 
        catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
        }        
        
        $res = $this->decodeResponse($response);

        return $res['token'];
    }
    
    private function decodeResponse($response) {
        $res = json_decode($response->getBody(), true);	
        if ($res['error']) {
            throw new \Exception( 'Ошибка '.$res['error']["code"].'. '.$res['error']["text"].'. '.json_encode($res) );
        }
        return $res;
    }
    
    public function report($uuid) {
        if (isset($this->params["test_mode"])) {
            $this->params['atol_group'] = 'v4-online-atol-ru_4179';
        }

        $token = $this->auth();
        
        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->request('GET', $this->getUrl().$this->params['atol_group'].'/report/'.$uuid, [
                'verify' => false,
                'headers' => [
                    'Token' => $token
                ]
            ]); 
        } 
        catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
        }          

        $res = $this->decodeResponse($response);
        return $res;        
    }

    public function addToQueue( $action, $receipt ) {
        self::getDbConnection()->insert('sale_atol_queue',[
            'date_create' => new \DateTime(),
            'is_sent'   => 0,
            'receipt'  => json_encode($receipt),
            'action'   => $action,
            'order_id' => $this->order->id,
        ],
        [
            'datetime'
        ]);

        return (int)self::getDbConnection()->lastInsertId();
    }
	
    public function checkStatusFromQueue( $id ) {
        $data = self::getDbConnection()->fetchAssoc('SELECT * FROM sale_atol_queue WHERE id=?',[$id]);
        if (!$data) return false;
        if ($data['status'] != 'wait' || !$data['uuid']) return false;

        if (isset($this->params["test_mode"])) {
            $this->params['atol_group'] = 'v4-online-atol-ru_4179';
        }

        $token = $this->auth();

        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->request('GET', $this->getUrl().$this->params['atol_group'].'/report/'.$data['uuid'], [
                'verify' => false,
                'headers' => [
                    'Token' => $token
                ],
            ]);
			
			$res = json_decode($response->getBody(), true);	
			
            self::getDbConnection()->update('sale_atol_queue',
                [
                    'response'  => $response->getBody(),
					'status'    => $res['status'],
                ],
                ['id' => $id]
            );
        }
        catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            self::getDbConnection()->update('sale_atol_queue',
                [

                    'response'  => $response->getBody(),
                ],
                ['id' => $id]
            );
        }

        $res = $this->decodeResponse($response);
        return $res;

    }	

    public function sendFromQueue($id, $external_id = null) {
        $data = self::getDbConnection()->fetchAssoc('SELECT * FROM sale_atol_queue WHERE id=?', [$id]);
        if (!$data) return false;
        if ($external_id == null && $data['is_sent']) return false;

        if (isset($this->params["test_mode"])) {
            $this->params['atol_group'] = 'v4-online-atol-ru_4179';
        }

        $token = $this->auth();

        $date = new \DateTime($data['date_create']);
        
        if ($external_id === null) {
            $external_id = (string)$data['order_id'] . '_' . $data['action'];
            if ($data['action'] == 'sell_refund') {
                $external_id .= $date->format('dmY_His');
            }
        }

        $params = [
            'external_id' => $external_id,
            'timestamp' => $date->format('d.m.Y H:i:s'),
            'receipt' => json_decode($data['receipt'], true),
        ];

        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->request('POST', $this->getUrl().$this->params['atol_group'].'/'.$data['action'], [
                'verify' => false,
                'headers' => [
                    'Token' => $token
                ],
                'json' => $params,
            ]);
            
            $res = json_decode($response->getBody(), true);   
            
            self::getDbConnection()->update('sale_atol_queue',
                [
                    'date_send' => new \DateTime(),
                    'is_sent'   => 1,
                    'success'   => 1,
                    'response'  => $response->getBody(),
                    'uuid'      => $res['uuid'],
                    'status'    => $res['status'],
                ],
                ['id' => $id],
                ['datetime']
            );
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            self::getDbConnection()->update('sale_atol_queue',
                [
                    'date_send' => new \DateTime(),
                    'is_sent'   => 1,
                    'response'  => $response->getBody(),
                ],
                ['id' => $id],
                ['datetime']
            );
        }

        $res = $this->decodeResponse($response);
        return $res;
    }
    
    public function sendReceiptRefund( $items = null ) {

        if (!$this->params['atol']) {
            return false;
        }

		$receipt = $this->getReceipt();
		
        if ($items !== null) {
            $amount = 0;
            $receipt['items'] = [];
            foreach ($items as $key => $item) {
                if ($item['quantity_refund'] <= 0) continue;
                
                $pid = explode('-', $item['id']);
                $product = \Sale\Product::getById($pid[0]);

                $receipt['items'][] = [
                    'name' => $item['name'],
                    'quantity' => floatval($item['quantity_refund']),
                    'price' => floatval($item['price']),
                    'sum' => $item['price']*$item['quantity_refund'],
                    'measurement_unit' => 'шт.',
                    'payment_method' => $product->atol_payment_method ? $product->atol_payment_method : $this->params['atol_payment_method'],
                    'payment_object' => $product->atol_payment_object ? $product->atol_payment_object : $this->params['atol_payment_object'],
                    'vat' => [
                        'type' => $product->atol_vat ? $product->atol_vat : $this->params['atol_vat'],
                    ],
                ];                
                
                $amount += floatval($item['quantity_refund']) * $item['price'];
            }
            $receipt['total'] = $amount;
            $receipt['payments'][0]['sum'] = $amount;
        }

        $id = $this->addToQueue('sell_refund', $receipt);
        return $this->sendFromQueue( $id );
        
    }
    
    public function sendReceiptSell($forceSend = false, $external_id = null) {
        if (!$this->params['atol']) {
            return false;
        }

        $id = $this->addToQueue('sell', $this->getReceipt());
        if ($forceSend){
            $this->sendFromQueue($id, $external_id);
        }
        return true;
    }
    
    public function getReceipt() {

        if (isset($this->params["test_mode"])) {
            $this->params['atol_inn'] = '5544332219';
            $this->params['atol_payment_address'] = 'https://v4.online.atol.ru';
        }

        $receipt = [
            'client'  => $this->getClient(),
            'company' => [
                'email' => $this->params['atol_email'],
                'sno' => $this->params['atol_sno'],
                'inn' => $this->params['atol_inn'],
                'payment_address' => $this->params['atol_payment_address'],
            ],
            'items' => $this->getItemsAtol(),
            'payments' => [
                [
                    'type' => 1,
                    'sum' => $this->order->getTotal(),
                ],
            ],
            'total' => $this->order->getTotal()
        ];
        
        return $receipt;
    }
    
    protected function getClient() {
        $data = [];
        $phone = preg_replace('/^\+7|\D/', '', $this->order->getPhone());
        if ($this->order->getEmail()) {
            $data['email'] = $this->order->getEmail();
        }
        if ($phone) {
            $data['phone'] = $phone;
        }        
        return $data;
    }
    
	protected function getItemsAtol() {
        $items = [];
        
        foreach ($this->order->getProducts() as $p) {
            $items[] = [
                'name' => $p['name'],
                'quantity' => floatval($p['quantity']),
                'price' => floatval($p['price']),
                'sum' => $p['price']*$p['quantity'],
                'measurement_unit' => 'шт.',
                'payment_method' => $p['product']->atol_payment_method ? $p['product']->atol_payment_method : $this->params['atol_payment_method'],
                'payment_object' => $p['product']->atol_payment_object ? $p['product']->atol_payment_object : $this->params['atol_payment_object'],
                'vat' => [
                    'type' => $p['product']->atol_vat ? $p['product']->atol_vat : $this->params['atol_vat'],
                ],
            ];
        }
        return $items;
    }  
}
