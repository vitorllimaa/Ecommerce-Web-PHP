<?php

namespace Map\model;

use Map\DB\Sql;
use Map\Model;
use Rain\Tpl\Exception;


class Product extends Model {

    public static function listAll(){

        $sql = new Sql();

        return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");

    }

    public function save(){
        
        $sql = new Sql();
        $result = $sql->select("CALL sp_products_save(:idproduct,:desproduct,:vlprice,:vlwidth,:vlheight,:vllength,:vlweight,:desurl)", 
            array(
                ":idproduct"=>$this->getidproduct(),
                ":desproduct"=>$this->getdesproduct(),
                ":vlprice"=>$this->getvlprice(),
                ":vlwidth"=>$this->getvlwidth(),
                ":vlheight"=>$this->getvlheight(),
                ":vllength"=>$this->getvllength(),
                ":vlweight"=>$this->getvlweight(),
                ":desurl"=>$this->getdesurl()
        ));
        $this->setData($result[0]);
    }

    public function delete($idproduct){
        $sql = new Sql();

        $result = $sql->select("DELETE FROM tb_products WHERE idproduct = :idproduct", array(
            ":idproduct"=>$idproduct
        ));
    }

    public function get($idproduct){
        $sql = new Sql();

        $result = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct",array(
            ":idproduct"=>$idproduct
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


}