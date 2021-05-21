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

$app = new Slim();
// post - envia para o banco
//get - envia para o html

$app->config('debug', true);

$app->get('/', function(){

     $page = new pagesite();
	 $page->setTpl("index");

});

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

$app->get("/categories/:idcategory", function($idcategory){

	$categories = new Categories();
	$categories->get((int)$idcategory);

	$page = new pagesite();
	$page->setTpl("category",[
		'category'=>$categories->getvalues(),
		'products'=>''
	]);

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

$app->run();