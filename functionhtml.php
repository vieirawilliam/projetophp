<?php
#FUNÇÃO PARA CORRIGIR VALOR
use \Hcode\Model\User;
use \Hcode\Model\Cart;

class FunctionHTML
{

    public function formatPrice(float $vlprice)
    {

        if (!$vlprice > 0) $vlprice = 0;
	    return number_format($vlprice, 2, ",", ".");
    }

    public function formatDate($date)
    {
        return date('d/m/Y', strtotime($date));
    }
    public function checkLogin($inadmin = true)
    {
        return User::checkLogin($inadmin);
    }
    public function getUserName()
    {
        $user = User::getFromSession();
        $a = $user->getdesperson();
        return $a;
    }
    public function getCartNrQtd()
    {
        $cart = Cart::getFromSession();
        $totals = $cart->getProductsTotals();
        return $totals['nrqtd'];
    }
    public function getCartVlSubTotal()
    {
        $cart = Cart::getFromSession();
        $totals = $cart->getProductsTotals();
        return $this->formatPrice($totals['vlprice']);
    }
}
