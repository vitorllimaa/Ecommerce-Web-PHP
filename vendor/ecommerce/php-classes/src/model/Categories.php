<?php

namespace Map\model;

use Map\DB\Sql;
use Map\Model;
use Rain\Tpl\Exception;


class Categories extends Model {

    public static function listAll(){

        $sql = new Sql();

        return $sql->select("SELECT * FROM tb_categories order BY descategory");

    }

    public function save($value){
        
        $sql = new Sql();

        $result = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
            ":idcategory"=>null,
            ":descategory"=>$value["descategory"]
        ));
        Categories::update();
    }

    public function delete($idcategory){
        $sql = new Sql();

        $result = $sql->select("DELETE FROM tb_categories WHERE idcategory = :idcategory", array(
            ":idcategory"=>$idcategory
        ));
        Categories::update();
    }

    public function get($idcategory){
        $sql = new Sql();

        $result = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory",array(
            ":idcategory"=>$idcategory
        )); 

        $this->setData($result[0]);
        
    }

    public function saveUpdate($descategory, $idcategory){
        
        $sql = new Sql();

        $result = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
            ":idcategory"=>$idcategory,
            ":descategory"=>$descategory["descategory"]

        ));
    }    

    public static function update(){

        $categories = Categories::listAll();

        $html = [];

        foreach ($categories as $values){
            array_push($html, '<li><a href="/categories/'.$values['idcategory'].'">'.$values['descategory'].'</a></li>');
            file_put_contents($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."categories-menu.html",
             implode('', $html));
        }

    }   

    public function getProducts($verify = true, $idcategory ){
        $sql = new Sql();
        
        if($verify === true){
        return $sql->select("SELECT * from tb_products where idproduct in(select a.idproduct from tb_products a inner join tb_categoriesproducts b 
        on a.idproduct = b.idproduct where b.idcategory = :idcategory)",array(
            ":idcategory"=>$idcategory
        )); 
        }else{
            return $sql->select("SELECT * from tb_products where idproduct not in(select a.idproduct from tb_products a inner join tb_categoriesproducts b 
        on a.idproduct = b.idproduct where b.idcategory = :idcategory)",array(
            ":idcategory"=>$idcategory
        )); 
        }
    }

    public function getProductPage($page = 1, $itemsPerPage = 8){
        $start = ($page - 1 ) * $itemsPerPage;
        $sql = new Sql();
        $result = $sql->select("SELECT sql_calc_found_rows* from tb_products a 
        inner join tb_categoriesproducts b on a.idproduct = b.idproduct
        inner join tb_categories c on c.idcategory = b.idcategory 
        where c.idcategory = :idcategory limit $start, $itemsPerPage", array(
            ":idcategory"=>$this->getidcategory()
        ));

        $resultTotal = $sql->select("SELECT found_rows() as nrtotal");

        return[
            'data'=>Product::checkList($result),
            'total'=>(int)$resultTotal[0]["nrtotal"],
            'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
        
        ];

    }

    public function addProduct($idcategory, $idproduct){

        $sql = new Sql();
        $sql->query("INSERT INTO tb_categoriesproducts values(:idcategory, :idproduct)",array(
            ':idcategory'=>$idcategory,
            ':idproduct'=>$idproduct
        ));
    }

    public function removeProduct($idcategory, $idproduct){

        $sql = new Sql();
        $sql->query("DELETE FROM tb_categoriesproducts WHERE idcategory = :idcategory AND idproduct = :idproduct",array(
            ':idcategory'=>$idcategory,
            ':idproduct'=>$idproduct
        ));
        
    }



}