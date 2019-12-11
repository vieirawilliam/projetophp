<?php

use Hcode\Page;
use hcode\Model\Category;

//ROTA DA PAGINA PRINCIPAL
$app->get('/', function() {
	$page = new Page();
	$page->setTpl("index");
});

#ROTA PARA ACESSAR A CATEGORIA
$app->get("/categories/:idcategory", function($idcategory){

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new Page();
	$page->setTpl("category",[
		'category'=>$category->getValues(),
		'products'=>[]
	]);

});
