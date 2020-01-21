<?php

use Hcode\PageAdmin;
use Hcode\Model\User;
use Hcode\Funcoes\Funcoes;

#ROTA PARA ALTERAR SENHA
$app->get("/admin/users/:iduser/password", function($iduser){

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$page = new PageAdmin();

	$page->setTpl("users-password", [
		"user"=>$user->getValues(),
		"msgError"=>User::getError(),
		"msgSuccess"=>User::getSuccess()
	]);

});

#ROTA POST PARA ALTERAR SENHA
$app->post("/admin/users/:iduser/password", function($iduser){

	User::verifyLogin();

	if (!isset($_POST['despassword']) || $_POST['despassword']==='') {
		User::setError("Preencha a nova senha.");
		header("Location: /admin/users/$iduser/password");
		exit;
	}

	if (!isset($_POST['despassword-confirm']) || $_POST['despassword-confirm']==='') {
		User::setError("Preencha a confirmação da nova senha.");
		header("Location: /admin/users/$iduser/password");
		exit;
	}

	if ($_POST['despassword'] !== $_POST['despassword-confirm']) {
		User::setError("Confirme corretamente as senhas.");
		header("Location: /admin/users/$iduser/password");
		exit;
	}

	$user = new User();

	$user->get((int)$iduser);

	$codif = new Funcoes();
	$password = $codif->codIF(strtoupper($_POST["despassword"]));
	$user->setPassword($password);

	User::setSuccess("Senha alterada com sucesso.");

	header("Location: /admin/users/$iduser/password");
	exit;

});

//ROTA PARA LISTAR TODOS OS USUÁRIOS
$app->get("/admin/users", function() {
	
	User::verifyLogin();
	
	$search = (isset($_GET['search'])) ? $_GET['search']:"";
	$page=(isset($_GET['page'])) ? (int)$_GET['page'] : 1;
	
	if ($search != '') {
		$pagination = User::getPageSearch($search, $page);
	} else {
		$pagination = User::getPage($page);
	}

	$pages=[];

	for ($x = 0; $x < $pagination['pages']; $x++)
	{
		array_push($pages, [
			'href'=>'/admin/users?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1
		]);
	}

	$page = new PageAdmin();
	$page->setTpl("users", array(
		"users"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
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
