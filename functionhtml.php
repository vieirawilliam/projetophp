<?php
#FUNÇÃO PARA CORRIGIR VALOR

class FunctionHTML{

    public function formatPrice(float $vlprice){

        return number_format($vlprice, 2, ",",".");
    }

}