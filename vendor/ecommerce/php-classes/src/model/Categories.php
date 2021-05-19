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

    }

    public function delete($idcategory){
        $sql = new Sql();

        $result = $sql->select("DELETE FROM tb_categories WHERE idcategory = :idcategory", array(
            ":idcategory"=>$idcategory
        ));
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

        )); }

}