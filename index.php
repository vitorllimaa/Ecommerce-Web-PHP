<?php

session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Map\page;
use \Map\login;
use \Map\model\Categories;
use Map\model\Product;
use \Map\model\User;
use \Map\pagesite;
use \Map\model\Cart;

$app = new Slim();

function formatPrice(float $price){
	return number_format($price, 2, ",", ".");
}

$app->config('debug', true);

//Rotas site

$app->get('/', function(){

	$product = Product::listAll();
	$page = new pagesite();
	$category = categories::update();
	$page->setTpl("index",[
		"products"=>Product::checkList($product)
	]);

});

$app->get("/categories/:idcategory", function($idcategory){
    $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	$categories = new Categories();
	$categories->get((int)$idcategory); 
	$pagination = $categories->getProductPage($page);

	$pages = [];
	for ($i=1; $i<=$pagination['pages']; $i++){
		array_push($pages, [
			'link'=>'/categories/'.$idcategory.'?page='.$i,
			'page'=>$i
		]);
	}
	$page = new pagesite();
	$page->setTpl("category",[
		'category'=>$categories->getvalues(),
		'products'=>$pagination["data"],
		'pages'=>$pages
	]);

});

$app->get("/products/:desurl", function($desurl){

	$product = new Product();
	$detailProduct = $product->getFromURL($desurl);
    $categoryProduct = $product->getCategories($detailProduct[0]['idproduct']);
	$page = new pagesite();
	$page->setTpl("product-detail", array(
		'product'=>$detailProduct[0],
		'categories'=>$categoryProduct[0]
	));

});

$app->get("/cart", function(){

	$Cart = Cart::getFromSession();
	$cart = new Cart();
	$page = new pagesite();
	$allCart = $cart->getProduct($cart->getFromSessionID());
	$page->setTpl("cart", [
		'cart'=>$cart->getValues(),
		'products'=>$allCart
		]);

});

$app->get("/cart/:idproduct/add", function($idproduct){

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();
	
	$Cart = new Cart();
   
	$Cart->addProduct($product, $Cart->getFromSessionID());

	header("Location: /cart");
	exit;
});

$app->get("/cart/:idproduct/minus", function($idproduct){

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$cart = new Cart();

	$cart->removeProduct($product);

	header("Location: /cart");
	exit;
});

$app->get("/cart/:idproduct/remove", function($idproduct){

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();

	$cart = new Cart();

	$cart->removeProduct($product, true);

	header("Location: /cart");
	exit;
});

$app->post("/cart/freight", function(){

	$cart = Cart::getFromSession();
	$cart = new Cart();
	$cart->setFreight($_POST['zipcode']);

	header("Location: /cart");
	exit;
});

//rotas admin

$app->get('/admin', function() {

	User::verifyLogin();
	$page = new page();
	$page->setTpl("index");	
});

$app->get('/admin/login', function() {
    
	$page = new login([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("login"); 
});

$app->post('/admin/login', function(){

	User::login($_POST["login"], $_POST["password"]);
	$user = new User;
	header("Location: /admin");
	
	exit;
});

$app->get('/admin/logout', function(){

	User::logout();
	header("Location: /admin/login");
	exit;

});

$app->get('/admin/users', function(){
	
	User::verifyLogin();
	$users = User::listAll();
	$page = new page();
	$page->setTpl("users", array(
		"users"=>$users
	));

});

$app->get("/admin/users/create", function(){
	User::verifyLogin();
	$page = new page();
	$page->setTpl("users-create");

});

$app->get('/admin/users/:iduser/delete', function($id_login){
	User::verifyLogin();
	$user = new User;
	$user->delete($id_login);
    header("Location: /admin/users");
	exit;

});

$app->get('/admin/users/:iduser', function($id_login){
	User::verifyLogin();
    $user = new User;
	$user->get($id_login);
	$page = new page();
	$page->setTpl("users-update", array(
		"user"=>$user->getValues()
	));

});

$app->post("/admin/users/create", function(){
	User::verifyLogin();
	$user = new User;
	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;
	$user->setData($_POST);
	$user->save();
	header("Location: /admin/users");
	exit;

	
});

$app->post("/admin/users/:iduser", function($id_login){
	User::verifyLogin();
	$user = new User;
	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;
	$user->get((int)$id_login);
	$user->setData($_POST);
	$user->update($_POST);
	header("Location: /admin/users");
	exit;
	
});

$app->get("/admin/forgot", function(){
	User::logout();
	$page = new page([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot");

});

$app->post("/admin/forgot", function(){
	User::logout();
	$user = User::getforgot($_POST["email"]);

	header("Location: /admin/forgot/sent");
	exit;
});

 $app->get("/admin/forgot/sent", function(){
    User::logout();
	$page = new page([
		"header"=>false,
		"footer"=>false
	]);
	
	$page->setTpl("forgot-sent");

});

$app->get("/admin/forgot/reset", function(){
   
	User::logout();
	$user = User::validForgotDecrypt($_GET["code"]);
	$page = new page([
		"header"=>false,
		"footer"=>false]);	
    $page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));

});

$app->post("/admin/forgot/reset", function(){
    User::logout();
	$user = User::validForgotDecrypt($_POST["code"]);
	User::setForgotUser($user["iduser"], $_POST["password"]);
	header("Location: /admin/login");
	exit;
});

$app->get("/admin/categories", function(){
	
	User::verifyLogin();
	$page = new page();
	$categories =  Categories::listAll();
	
	$page->setTpl("categories",[
		"categories"=>$categories
	]);
});

$app->get("/admin/categories/create", function(){
	User::verifyLogin();
	$page = new page();
	$categories =  Categories::listAll();
	
	$page->setTpl("categories-create",[
		"categories"=>$categories
	]);
});

$app->post("/admin/categories/create", function(){
    User::verifyLogin();
	$categories = new Categories(); 
	$categories->save($_POST);
	header("Location: /admin/categories");
	exit;
});

$app->get("/admin/categories/:idcategory/delete", function($idcategory){
	User::verifyLogin();
	$categories = new Categories(); 
	$categories->delete((int)$idcategory);

	header("Location: /admin/categories");
	exit;
});

$app->get("/admin/categories/:idcategor", function($idcategory){
	User::verifyLogin();
	$page = new page();
	$categories = new Categories();
	$categories->get((int)$idcategory);

	$page->setTpl("categories-update",[
		"category"=>$categories->getValues()
	]);	
});

$app->post("/admin/categories/:idcategor", function($idcategory){
    User::verifyLogin();
	$categories = new Categories(); 
	$categories->saveUpdate($_POST, $idcategory);
	header("Location: /admin/categories");
	exit;
});

$app->get("/admin/products", function(){

    $Product = Product::listAll();
	$page = new page();
	$page->setTpl("products",[
		"products"=>$Product
	]);

});

$app->get("/admin/products/create", function(){
	User::verifyLogin();
	$page = new page();
	$page->setTpl("products-create");

});

$app->post("/admin/products/create", function(){
    User::verifyLogin();
	$Product = new Product(); 
	$Product->setData($_POST);
	$Product->save();
	header("Location: /admin/products");
	exit;
});

$app->get("/admin/products/:idproduct", function($idproduct){
	
	User::verifyLogin();
	$product = new Product();
	$product->get($idproduct);
	$page = new page();
	$page->setTpl("products-update",[
		"product"=>$product->getvalues()
	]);

});

$app->post("/admin/products/:idproduct", function($idproduct){
	
	User::verifyLogin();
	$product = new Product();
	$product->get($idproduct);
	$product->setData($_POST);
	$product->save();
	$product->setPhoto($_FILES["file"]);
	header('Location: /admin/products');
	exit;

});

$app->get("/admin/products/:idproduct/delete", function($idproduct){
	
	User::verifyLogin();
	$product = new Product();
	$product->delete($idproduct);
	header('Location: /admin/products');
	exit;
});

$app->get("/admin/categories/:idproduct/products", function($idcategory){
	
	User::verifyLogin();
	$category = new Categories();
	$category->get($idcategory);
	$page = new page();
	$page->setTpl("categories-products",[
		"category"=>$category->getvalues(),
		"productsRelated"=>$category->getProducts(true, $idcategory),
		"productsNotRelated"=>$category->getProducts(false, $idcategory)
	]);
});

$app->get("/admin/categories/:idcategory/products/:idproduto/add", function($idcategory, $idproduct){
	User::verifyLogin();
	$category = new Categories();
	$category->addProduct($idcategory, $idproduct);
	header("Location: /admin/categories/".$idcategory."/products");
	exit;
});

$app->get("/admin/categories/:idcategory/products/:idproduto/remove", function($idcategory, $idproduct){
	
	User::verifyLogin();
	$category = new Categories();
	$category->get($idcategory);
	$category->removeProduct($idcategory, $idproduct);
	header("Location: /admin/categories/".$idcategory."/products");
	exit;
});

$app->run();
