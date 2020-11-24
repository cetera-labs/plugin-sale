<?php
namespace Sale;

class WidgetCart extends \Cetera\Widget\Templateable
{

    protected $_params = array(
		'order_url'     => '/order/',
		'catalog_url'   => false,
		'show_coupon'   => false,
		'clear_button'  => true,
    ); 
	
	public $coupon_error = null;

	protected function init()
	{
		$this->application->addScript('/plugins/sale/locale.php?locale='.$this->application->getLocale());
		$this->application->addScript('/cms/plugins/sale/js/common.js');
		
		if (!$this->widgetTitle) $this->widgetTitle = '<div class="row column"><h1>'.$this->t->_('Корзина').'</h1></div>';
		
		if (isset($this->application->getSession()->saleOrderCreated)) {
			unset( $this->application->getSession()->saleOrderCreated );
		}		
		if (!$this->widgetTitle) $this->widgetTitle = $this->t->_('Корзина');
		
		if (!$this->getCart()->getProductsCount()) {
			$this->setParam('cart_is_empty', 1);
		}
		if (isset($_POST['coupon'])) try {
			$this->getCart()->addCoupon($_POST['coupon']);
		}
		catch (\Exception $e) {
			$this->coupon_error = $e->getMessage();
		}
	}		

	public function getCart()
	{
		return \Sale\Cart::get();
	}
    
    public function getProductsCount() {
        return '<span class="x-total-count">'.$this->getCart()->getProductsCount().'</span>';
    }
    
    public function getTotalFull() {
        return '<span class="x-total-full">'.$this->getCart()->getTotalFull(1).'</span>';
    }  

    public function getTotal() {
        return '<span class="x-total-sum">'.$this->getCart()->getTotal(1).'</span>';
    }  

    public function getDiscountTotal() {
        return '<span class="x-total-discount">'.$this->getCart()->getDiscountTotal(1).'</span>';
    }     
	
}