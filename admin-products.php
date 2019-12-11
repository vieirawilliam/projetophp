<?php

use Hcode\PageAdmin;
use Hcode\Model\Product;
use Hcode\Model\User;

#ROTA GET PARA LISTA DE CATEGORIAS
$app->get("/admin/products", function(){

	User::verifyLogin();

	$products = Product::listAll();
	
	$page = new PageAdmin();
	$page->setTpl("products", [
		"products"=>$products
	]);

});

#ROTA GET PARA CRIAR CATEGORIAS
$app->get("/admin/products/create", function(){
	User::verifyLogin();
	
	$page = new PageAdmin();
	$page->setTpl("products-create");

});

#ROTA GET PARA CRIAR CATEGORIAS
$app->post("/admin/products/create", function(){
	User::verifyLogin();

	$page = new PageAdmin();
	$page->setTpl("products-create");

	$product = new Product();

	$product->setData($_POST);

	$product->save();

	header('Location: /admin/products');

	exit;

});

#ROTA DELETE DE CATEGORIAS
$app->get("/admin/products/:idproduct/delete", function($idproduct){

	User::verifyLogin();

	$product = new Product();

	$product->get((int)$idproduct);

	$product->delete();

	header('Location: /admin/products');

	exit;
});

#ROTA ALTERAR DE CATEGORIAS
$app->get("/admin/products/:idproduct", function($idproduct){
	
	User::verifyLogin();

	$product = new Product();

	$product->get((int)$idproduct);

	$page = new PageAdmin();

	$page->setTpl("products-update",[
		'product'=>$product->getValues()
	]);
});

#ROTA SALVA ALTERACAO DE CATEGORIAS
$app->post("/admin/products/:idproduct", function($idproduct){
	
	User::verifyLogin();

	$product = new Product();

	$product->get((int)$idproduct);

	$product->setData($_POST);

	$product->save();

	$product->setPhoto($_FILES["file"]);

	header('Location: /admin/products');

	exit;
});