<?php
namespace Sale\Api;

use Zend\View\Model\JsonModel;

class WishlistController extends AbstractController
{
    public function defaultAction()
    {
        $this->checkAuth();
        $wishlist = \Sale\WishList::get();
        return $this->wlContents($wishlist);
    }
   
    public function addAction()
    {
        $this->checkAuth();
        $wishlist = \Sale\WishList::get();
        $params = $this->getEvent()->getRouteMatch()->getParams();
        $product = \Sale\Product::getById((int)$params['id']);
        $wishlist->addProduct( $product );
        
        return $this->wlContents($wishlist);
    }
    
    public function removeAction()
    {
        $this->checkAuth();
        $wishlist = \Sale\WishList::get();
        $params = $this->getEvent()->getRouteMatch()->getParams();
        $product = \Sale\Product::getById((int)$params['id']);
        $wishlist->removeProduct( $product );
        
        return $this->wlContents($wishlist);
    } 

    public function clearAction()
    {
        $this->checkAuth();
        $wishlist = \Sale\WishList::get();
        $wishlist->clear();
        
        return $this->wlContents($wishlist);
    }    

    private function wlContents($wishlist) {
        $res = [
            'success' => true,
            'data' => [
                'items' => $wishlist->getProducts()->asArray(['id','name','pic','price']),
            ],
            

        ]; 
        return new JsonModel( $res );         
    }
       
}