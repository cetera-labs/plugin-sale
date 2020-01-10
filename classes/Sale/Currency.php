<?php
namespace Sale;

class Currency extends \Cetera\Base {
	
	use \Cetera\DbConnection;
	
	protected static $instances = array();
	
	protected $_name;
	protected $_code;
	protected $_template;
	protected $_rate;
	protected $_rate_cnt;
	protected $_prime;
	
	public static function getByCode($code) {
		
		if (!isset(self::$instances[$code])) {
			
			if ($code === -1) {
				$f = self::getDbConnection()->fetchAssoc('SELECT * FROM sale_currency WHERE prime=1');
			}
			else {
				$f = self::getDbConnection()->fetchAssoc('SELECT * FROM sale_currency WHERE code=?', [$code]);
			}
			if (!$f) throw new \Exception('Currency "'.$code.'" is not found');
			self::$instances[$code] = new self($f);
			
		}
		return self::$instances[$code];
		
	}
	
	// Базовая валюта
	public static function getDefault() {		
		return self::getByCode(-1);		
	}	
	
    public function __toString() {
        return $this->code;
    } 	
	
	public function format($value, $currency = null)
	{
		if ($currency) {
			$value = $this->convert($value, $currency);
		}
		$value = number_format($value,2,',','&nbsp;');
		$value = str_replace(',00','',$value);
		$value = str_replace('#', $value, $this->template);
		return $value;
	}

	public function convert($value, $currency)
	{
		if ($this->id == $currency->id) {
			return $value;
		}
		
		$value = $value * ($currency->rate / $currency->rate_cnt) / ($this->rate / $this->rate_cnt);			
		return $value;

	}
	
}