<?php 
session_start();
require_once("vendor/autoload.php");

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

$app->post('/admin/login', function() {

	$login = strtoupper( filter_input(INPUT_POST, 'login', FILTER_DEFAULT) );
	$password = strtoupper( filter_input(INPUT_POST, 'password', FILTER_DEFAULT) );

	User::login($login, $password);

	header("Location: /admin");
	exit;
});

$app->get('/admin/logout', function() {

	User::logout();

	header("Location: /admin/login");
	exit;

});



$app->run();

 ?>