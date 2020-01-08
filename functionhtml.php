<?php
#FUNÇÃO PARA CORRIGIR VALOR

class FunctionHTML{

    public function formatPrice(float $vlprice){

        if($vlprice != ''){
            return number_format($vlprice, 2, ",",".");
        }else{
            $vlprice = 0;
            return number_format($vlprice, 2, ",",".");
        }
    
    }

}