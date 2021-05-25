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