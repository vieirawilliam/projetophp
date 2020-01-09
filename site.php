<?php

use Hcode\Page;
use Hcode\Model\Category;
use Hcode\Model\Product;
use \Hcode\Funcoes\Funcoes;
use Hcode\Model\Cart;
use Hcode\Model\User;
use Hcode\Model\Address;

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

#ROTA PARA CARRINHO DE COMPRAS
$app->get("/cart", function(){
	$cart = Cart::getFromSession();
	$page = new Page();
	$page->setTpl("cart", [
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Cart::getMsgError()
	]);
});

#ROTA PARA ADICIONA PRODUTOS CARRINHOS
$app->get("/cart/:idproduct/add", function($idproduct){
	
	$product = new Product();
	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();
	
	$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;
	
	for($i = 0; $i < $qtd; $i++){
		$cart->addProduct($product);
	}

	header("Location: /cart");
	exit;
});

#ROTA PARA REMOVE ONE PRODUTOS CARRINHOS
$app->get("/cart/:idproduct/minus", function($idproduct){
	
	$product = new Product();
	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();
	$cart->removeProduct($product);

	header("Location: /cart");
	exit;
});

#ROTA PARA REMOVE ALL PRODUTOS CARRINHOS
$app->get("/cart/:idproduct/remove", function($idproduct){
	
	$product = new Product();
	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();
	$cart->removeProduct($product, true);

	header("Location: /cart");
	exit;
});

#ROTA PARA CALCULAR FRETE
$app->post("/cart/freight", function(){
	
	$cart = Cart::getFromSession();
	$cart->setFreight($_POST['zipcode']);
	header("Location: /cart");
	exit;
});

#ROTA PARA CALCULAR FRETE
$app->post("/cart/freight", function(){
	
	$cart = Cart::getFromSession();
	$cart->setFreight($_POST['zipcode']);
	header("Location: /cart");
	exit;
});

#ROTA PARA LOGIN DE USUARIO DO SITE
$app->get("/checkout", function(){
	
	User::verifyLogin(false);
	
	$address = new Address();
	$cart = Cart::getFromSession();
	if (!isset($_GET['zipcode'])) {
		$_GET['zipcode'] = $cart->getdeszipcode();
	}
	if (isset($_GET['zipcode'])) {
		$address->loadFromCEP($_GET['zipcode']);
		$cart->setdeszipcode($_GET['zipcode']);
		$cart->save();
		$cart->getCalculateTotal();
	}
	if (!$address->getdesaddress()) $address->setdesaddress('');
	if (!$address->getdesnumber()) $address->setdesnumber('');
	if (!$address->getdescomplement()) $address->setdescomplement('');
	if (!$address->getdesdistrict()) $address->setdesdistrict('');
	if (!$address->getdescity()) $address->setdescity('');
	if (!$address->getdesstate()) $address->setdesstate('');
	if (!$address->getdescountry()) $address->setdescountry('');
	if (!$address->getdeszipcode()) $address->setdeszipcode('');
	$page = new Page();
	$page->setTpl("checkout", [
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Address::getMsgError()
	]);
});

#ROTA LOGIN DO SITE
$app->get("/login", function(){
	$page = new Page();
	$page->setTpl("login", [
		'error'=>User::getError(),
		'errorRegister'=>User::getErrorRegister(),
		'registerValues'=>(isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ['name'=>'', 'email'=>'', 'phone'=>'']
	]);
});

#ROTA LOGIN DO SITE VIA POST
$app->post("/login", function(){
	
	try {
		User::login($_POST['login'], $_POST['password']);
	} catch(Exception $e) {
		User::setError($e->getMessage());
	}
	header("Location: /checkout");
	exit;
});

#ROTA DE LOGOUT DO SITE
$app->get("/logout", function(){
	User::logout();
	header("Location: /login");
	exit;
});

#RODA CADASTRO DO USUARIO DO SITE
$app->post("/register", function(){
	
	$_SESSION['registerValues'] = $_POST;
	if (!isset($_POST['name']) || $_POST['name'] == '') {
		User::setErrorRegister("Preencha o seu nome.");
		header("Location: /login");
		exit;
	}
	if (!isset($_POST['email']) || $_POST['email'] == '') {
		User::setErrorRegister("Preencha o seu e-mail.");
		header("Location: /login");
		exit;
	}
	
	if (User::checkLoginExist($_POST['email']) === true) {
		User::setErrorRegister("Este endereço de e-mail já está sendo usado por outro usuário.");
		header("Location: /login");
		exit;
	}
	
	if (!isset($_POST['password']) || $_POST['password'] == '') {
		User::setErrorRegister("Preencha a senha.");
		header("Location: /login");
		exit;
	}
	
	$user = new User();
	$user->setData([
		'inadmin'=>0,
		'deslogin'=>$_POST['email'],
		'desperson'=>$_POST['name'],
		'desemail'=>$_POST['email'],
		'despassword'=>$_POST['password'],
		'nrphone'=>$_POST['phone']
	]);
	$user->save();
	User::login($_POST['email'], $_POST['password']);
	header('Location: /checkout');
	exit;
});

#ROTA GER PARA O FORGOT
$app->get("/forgot", function() {
	$page = new Page();
	$page->setTpl("forgot");
});

#ROTA POST PARA O FORGOT
$app->post("/forgot",function(){
	$user = User::getForgot($_POST["email"],false);

	header("Location: /forgot/sent");
	exit;
});

#ROTA GER PARA O FORGOT
$app->get("/forgot/sent", function() {
	$page = new Page();
	$page->setTpl("forgot-sent");
});

#ROTA GET DE RESET PARA O FORGOT
$app->get("/forgot/reset", function() {
	
	$user = User::validForgotDecrypt($_GET["code"]);
	
	$page = new Page();
	$page->setTpl("forgot-reset",array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));
});

#ROTA POST ALTERA SENHA FORGOT
$app->post("/forgot/reset", function(){
	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$codif = new Funcoes();

	$password = $codif->codIF(strtoupper($_POST["password"]));

	$user->setPassword($password);

	$page = new Page();
	$page->setTpl("forgot-reset-success");

});

#ROTA GET PARA O PROFILE
$app->get("/profile", function(){
	
	User::verifyLogin(false);
	$user = User::getFromSession();
	$page = new Page();
	$page->setTpl("profile", [
		'user'=>$user->getValues(),
		'profileMsg'=>User::getSuccess(),
		'profileError'=>User::getError()
	]);
});

#ROTA POST PARA PROFILE
$app->post("/profile", function(){
	User::verifyLogin(false);
	if (!isset($_POST['desperson']) || $_POST['desperson'] === '') {
		User::setError("Preencha o seu nome.");
		header('Location: /profile');
		exit;
	}
	if (!isset($_POST['desemail']) || $_POST['desemail'] === '') {
		User::setError("Preencha o seu e-mail.");
		header('Location: /profile');
		exit;
	}
	$user = User::getFromSession();
	if ($_POST['desemail'] !== $user->getdesemail()) {
		if (User::checkLoginExist($_POST['desemail']) === true) {
			User::setError("Este endereço de e-mail já está cadastrado.");
			header('Location: /profile');
			exit;
		}
	}
	$_POST['inadmin'] = $user->getinadmin();
	
	$func = new Funcoes();

	$senha = $func->decodIF($user->getdespassword());

	$_POST['despassword'] = $senha;
	$_POST['deslogin'] = $_POST['desemail'];
	$user->setData($_POST);
	$user->update();
	User::setSuccess("Dados alterados com sucesso!");
	header('Location: /profile');
	exit;
});
