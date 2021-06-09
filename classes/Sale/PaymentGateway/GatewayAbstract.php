<?php
namespace Sale\PaymentGateway;

abstract class GatewayAbstract  {
	
	use \Cetera\DbConnection;
	
	public $params;
	protected $t = NULL;
	public $order;	
	
	public function __construct($params, $order)
	{
		$this->params = $params;
		$this->order = $order;	
		$this->t = \Cetera\Application::getInstance()->getTranslator();
	}
	
	public static function getInfo() {
		return [];
	}
	
	abstract public function pay( $return = '' );
    
    public static function isRefundAllowed() {
        return false;
    }
    
    public function refund( $items = null ) {
        throw new \Exception('Возврат средств не реализован');
    }
    
    public function cancel() {
    }    
    
	public function getStatus() {
        throw new \Exception('Нельзя получить статус заказа');
	}    
    
	public function checkIfTransactionHasAlreadyBeenProcessed($txn_id)
	{
		$data = self::getDbConnection()->fetchArray('SELECT COUNT(*) FROM sale_payment_transactions WHERE transaction_id=? and gateway=?',array($txn_id,get_class($this)));
		return $data[0]>0;
	}
	
	public function saveTransaction($txn_id, $data)
	{
		//if ($this->checkIfTransactionHasAlreadyBeenProcessed($txn_id)) {
		//	return false;
		//}
		
		self::getDbConnection()->insert('sale_payment_transactions',array(
			'date'             => new \DateTime(),		
			'order_id'         => $this->order->id,
			'transaction_id'   => $txn_id,
			'gateway'          => get_class($this),
			'data'             => serialize($data)
		), array('datetime'));
		
		return true;
	}	

	public function getOrderByTransaction($txn_id)
	{
        return self::getDbConnection()->fetchColumn('SELECT order_id FROM sale_payment_transactions WHERE transaction_id=? and gateway=?',[$txn_id,get_class($this)]);
    }  

	public function getTransactions()
	{
        $data = self::getDbConnection()->fetchAll('SELECT * FROM sale_payment_transactions WHERE order_id=? and gateway=? ORDER BY date',[$this->order->id,get_class($this)]);
        foreach ($data as $key => $value) {
            $data[$key]['data'] = unserialize($value['data']);
        }
        return $data;
    }      
	
}