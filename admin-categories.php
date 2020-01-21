<?php

use Hcode\PageAdmin;
use Hcode\Model\Category;
use Hcode\Model\Product;
use Hcode\Model\User;

#ROTA GET PARA LISTA DE CATEGORIAS
$app->get("/admin/categories", function(){

	User::verifyLogin();

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	if ($search != '') {

		$pagination = Category::getPageSearch($search, $page);

	} else {

		$pagination = Category::getPage($page);

	}

	$pages = [];

	for ($x = 0; $x < $pagination['pages']; $x++)
	{

		array_push($pages, [
			'href'=>'/admin/categories?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1
		]);

	}

	$page = new PageAdmin();

	$page->setTpl("categories", [
		"categories"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
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

#ROTA CATEGORIAS VS PRODUTOS
$app->get("/admin/categories/:idcategory/products", function($idcategory){
	
	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();

	$page->setTpl("categories-products",[
		'category'=>$category->getValues(),
		'productsNotRelated'=>$category->getProducts(false),
		'productsRelated'=>$category->getProducts()
	]);
});

#ROTA ADD CATEGORIAS VS PRODUTOS
$app->get("/admin/categories/:idcategory/products/:idproducts/add", function($idcategory,$idproduct){
	
	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$product = new Product();

	$product->get((int)$idproduct);

	$category->addProduct($product);

	header("Location: /admin/categories/".$idcategory."/products");
	exit;
	
});

#ROTA ADD CATEGORIAS VS PRODUTOS
$app->get("/admin/categories/:idcategory/products/:idproducts/remove", function($idcategory,$idproduct){
	
	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$product = new Product();

	$product->get((int)$idproduct);

	$category->removeProduct($product);

	header("Location: /admin/categories/".$idcategory."/products");
	exit;
	
});
