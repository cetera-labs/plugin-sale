<?php

namespace Sale;

trait PriceDecimals
{
    protected static $priceDecimals = null;

    public function getPriceDecimals()
    {
        if (self::$priceDecimals === null) {
            self::$priceDecimals = \Sale\Setup::configGet( 'price_decimals' );
            if (self::$priceDecimals === null) {
                self::$priceDecimals = 2;
            }
        }
        return self::$priceDecimals;
    }

    public function roundPrice($price)
    {
        return round($price, $this->getPriceDecimals());
    }
}