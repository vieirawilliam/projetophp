<?php 
session_start();
require_once("vendor/autoload.php");

use Hcode\Funcoes\Funcoes;
use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app = new Slim();

$app->config('debug', true);

//ROTA DA PAGINA PRINCIPAL
$app->get('/', function() {
	$page = new Page();
	$page->setTpl("index");
});

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

//ROTA PARA LISTAR TODOS OS USUÁRIOS
$app->get("/admin/users", function() {
	User::verifyLogin();
	$users = User::listAll();

	$page = new PageAdmin();
	$page->setTpl("users", array(
		"users"=>$users
	));
});

#ROTA PARA CRIAR O USUARIUO
$app->get("/admin/users/create", function() {
	User::verifyLogin();
	
	$page = new PageAdmin();
	$page->setTpl("users-create");

});

#ROTA PARA DELETE USUARIO
$app->get("/admin/users/:iduser/delete", function($iduser) {
	User::verifyLogin();

	$user = new user();

	$user->get((int)$iduser);

	$user->delete();

	header("Location: /admin/users");
	exit;

});

#ROTA PARA ALTERAR O USUARIO
$app->get("/admin/users/:iduser", function($iduser){
	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$page= new PageAdmin();
	$page->setTpl("users-update", array(
		"user"=>$user->getValues()
	));
});

#ROTA PARA SALVAR O USUÁRIO
$app->post("/admin/users/create", function() {
	User::verifyLogin();
	
	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	$user->setData($_POST);

	$user->save();

	header("Location: /admin/users");

	exit;
	
});

#ROTA PARA SALVAR A ALTERACAO DO USUARIO
$app->post("/admin/users/:iduser", function($iduser) {
	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	$user->get((int)$iduser);

	$user->setData($_POST);

	$user->update();

	header("Location: /admin/users");

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


$app->post("/admin/forgot/reset", function(){
	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setFogotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$codif = new Funcoes();

	$password = $codif->codIF($_POST["password"]);

	$user->setPassword($password);

	$page = new PageAdmin(
		["header"=>false,
		"footer"=>false
		]);
	$page->setTpl("forgot-reset-success");

});


$app->run();
