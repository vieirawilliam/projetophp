<?php

use Hcode\Page;
use Hcode\Model\Category;
use Hcode\Model\Product;
use \Hcode\Funcoes\Funcoes;

//ROTA DA PAGINA PRINCIPAL
$app->get('/', function() {
	
	$products = Product::listAll();
	$page = new Page();
	$page->setTpl("index", [
		'products'=>Product::checkList($products)
	]);

});

#ROTA PARA ACESSAR A CATEGORIA
$app->get("/categories/:idcategory", function($idcategory){
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
	$category = new Category();
	$category->get((int)$idcategory);
	$pagination = $category->getProductsPage($page);
	$pages = [];
	for ($i=1; $i <= $pagination['pages']; $i++) { 
		array_push($pages, [
			'link'=>'/categories/'.$category->getidcategory().'?page='.$i,
			'page'=>$i
		]);
	}
	$page = new Page();
	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>$pagination["data"],
		'pages'=>$pages
	]);
});

#ROTA PARA DETALHES DO PRODUTOS
$app->get("/products/:desurl", function($desurl){

	$product = new Product();

	$product->getFromURL($desurl);

	$page = new Page();

	$page->setTpl("product-detail", [
		'product'=>$product->getValues(),
		'categories'=>$product->getCategories()
	]);

});
