<?php

use Hcode\PageAdmin;
use Hcode\Model\User;
use Hcode\Funcoes\Funcoes;

//ROTA DA PAGINA DE ADMINISTRAÇÃO
$app->get('/admin', function() {
	User::verifyLogin();
	
	$page = new PageAdmin();
	$page->setTpl("index");
});

//ROTA DA PAGINA DE ADMINISTRAÇÃO
$app->get('/admin/login', function() {
	$page = new PageAdmin(
		["header"=>false,
		"footer"=>false
		]);
	$page->setTpl("login");
});

//ROTA PARA TELA DE LOGIN
$app->post('/admin/login', function() {

	$login = strtoupper( filter_input(INPUT_POST, 'login', FILTER_DEFAULT) );
	$password = strtoupper( filter_input(INPUT_POST, 'password', FILTER_DEFAULT) );

	User::login($login, $password);

	header("Location: /admin");
	exit;
});

//ROTA PARA DESLOGAR DO SISTEMAS
$app->get('/admin/logout', function() {

	
	User::logout();

	header("Location: /admin/login");
	exit;

});

#ROTA GER PARA O FORGOT
$app->get("/admin/forgot", function() {
	$page = new PageAdmin(
		["header"=>false,
		"footer"=>false
		]);
	$page->setTpl("forgot");
});

#ROTA POST PARA O FORGOT
$app->post("/admin/forgot",function(){
	$user = User::getForgot($_POST["email"]);

	header("Location: /admin/forgot/sent");
	exit;
});

#ROTA GER PARA O FORGOT
$app->get("/admin/forgot/sent", function() {
	$page = new PageAdmin(
		["header"=>false,
		"footer"=>false
		]);
	$page->setTpl("forgot-sent");
});

#ROTA GET DE RESET PARA O FORGOT
$app->get("/admin/forgot/reset", function() {
	
	$user = User::validForgotDecrypt($_GET["code"]);
	
	$page = new PageAdmin(
		["header"=>false,
		"footer"=>false
		]);
	$page->setTpl("forgot-reset",array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));
});

#ROTA POST ALTERA SENHA FORGOT
$app->post("/admin/forgot/reset", function(){
	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$codif = new Funcoes();
	$password = $codif->codIF(strtoupper($_POST["password"]));
	$user->setPassword($password);

	$page = new PageAdmin(
		["header"=>false,
		"footer"=>false
		]);
	$page->setTpl("forgot-reset-success");

});
