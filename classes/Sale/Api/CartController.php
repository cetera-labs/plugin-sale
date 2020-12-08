<?php
namespace Sale\Api;

use Zend\View\Model\JsonModel;

class CartController extends AbstractController
{
    public function defaultAction()
    {
        try {
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

            $res = [
                'success' => true,
                'data' => [
                    'items' => array_map(function($item){
                                    return [
                                        'id' => $item['product']->id,
                                        'name' => $item['product']->name,
                                        'pic' => $item['product']->pic,
                                        'price' => $item['price'],
                                        'quantity' => $item['quantity'],
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
        }
        catch (\Exception $e) {
            $res['success'] = false;
            $res['error']['message'] = $e->getMessage();
        } 
        return new JsonModel( $res );         
    }
   
    public function addAction()
    {
        
        try {
            $this->checkAuth();
            $cart = \Sale\Cart::get();
            $params = $this->getEvent()->getRouteMatch()->getParams();
            $product = \Sale\Product::getById((int)$params['id']);
            if (!$product->price) {
                throw new \Exception('Этот товар нельзя купить');
            }
            $cart->addProduct( $product );
            
            $res = [
                'success' => true,
                'count' => $cart->getProductsCount(),
            ]; 
        }
        catch (\Exception $e) {
            $res['success'] = false;
            $res['error']['message'] = $e->getMessage();
        } 
        return new JsonModel( $res ); 
        
    }
       
}