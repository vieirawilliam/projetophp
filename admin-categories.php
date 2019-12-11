<?php

use Hcode\PageAdmin;
use Hcode\Model\Category;
use Hcode\Model\User;

#ROTA GET PARA LISTA DE CATEGORIAS
$app->get("/admin/categories", function(){

	User::verifyLogin();

	$categories = Category::listAll();
	
	$page = new PageAdmin();
	$page->setTpl("categories", [
		"categories"=>$categories
	]);

});

#ROTA GET PARA CRIAR CATEGORIAS
$app->get("/admin/categories/create", function(){
	User::verifyLogin();
	
	$page = new PageAdmin();
	$page->setTpl("categories-create");

});

#ROTA GET PARA CRIAR CATEGORIAS
$app->post("/admin/categories/create", function(){
	User::verifyLogin();

	$page = new PageAdmin();
	$page->setTpl("categories-create");

	$category = new Category();

	$category->setData($_POST);

	$category->save();

	header('Location: /admin/categories');

	exit;

});

#ROTA DELETE DE CATEGORIAS
$app->get("/admin/categories/:idcategory/delete", function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->delete();

	header('Location: /admin/categories');

	exit;
});

#ROTA ALTERAR DE CATEGORIAS
$app->get("/admin/categories/:idcategory", function($idcategory){
	
	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();

	$page->setTpl("categories-update",[
		'category'=>$category->getValues()
	]);
});

#ROTA SALVA ALTERACAO DE CATEGORIAS
$app->post("/admin/categories/:idcategory", function($idcategory){
	
	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->setData($_POST);

	$category->save();

	header('Location: /admin/categories');

	exit;
});