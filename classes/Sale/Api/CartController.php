<?php
namespace Sale\Api;

use Zend\View\Model\JsonModel;

class CartController extends AbstractController
{
    public function defaultAction()
    {            
        $this->checkAuth();
        $cart = \Sale\Cart::get();
        
        if ($this->params['coupon']) {
            $cart->addCoupon($this->params['coupon']);
        }
        if ($this->params['item']) {
            $product = \Sale\Product::getById((int)$this->params['item']['id']);
            if (!$product->price) {
                throw new \Exception('Этот товар нельзя купить');
            }                
            $cart->setProduct( $product, (int)$this->params['item']['quantity'] );
        }

        return $this->cartContents($cart);        
    }
   
    public function addAction()
    {
        
        $this->checkAuth();
        $cart = \Sale\Cart::get();
        $params = $this->getEvent()->getRouteMatch()->getParams();
        $product = \Sale\Product::getById((int)$params['id']);
        if (!$product->price) {
            throw new \Exception('Этот товар нельзя купить');
        }
        $cart->addProduct( $product );
        
        return $this->cartContents($cart);
    }
    
    public function setAction()
    {
        $this->checkAuth();
        $cart = \Sale\Cart::get();
        $cart->clear();
        
        if (isset($this->params['items']) && is_array($this->params['items'])) {
            foreach($this->params['items'] as $product) {
                $p = \Sale\Product::getById((int)$product['id']);
                if (!$p->price) {
                    continue;
                } 
                $cart->addProduct( $p, $product['quantity'] );                
            }
        }
        
        return $this->cartContents($cart);
        
    }    
    
    private function cartContents($cart) {
        $res = [
            'success' => true,
            'data' => [
                'items' => array_map(function($item){
                                return [
                                    'id' => $item['product']->id,
                                    'name' => $item['product']->name,
                                    'pic' => $item['product']->pic,
                                    'price' => $item['price'],
                                    'quantity' => (int)$item['quantity'],
                                    'sum' => $item['sum'],
                                ];
                            },$cart->getProducts()),
                'coupons'  => array_map(function($item){
                                return [
                                    'code' => $item['code'],
                                ];
                            },array_values($cart->getCoupons())),
                'total'    => $cart->getTotal(),
                'discount' => $cart->getDiscountTotal(),
            ],
            

        ]; 
        return new JsonModel( $res );         
    }    
       
}