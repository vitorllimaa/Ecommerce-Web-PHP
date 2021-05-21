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

    public static function checkList($list){

        foreach ($list as &$row){

            $p = new Product();
            $p-> setData($row);
            $row = $p->getValues();
        }

        return $list;
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

    public function checkPhone(){

        if(file_exists($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.
        'res'.DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.
        'img'.DIRECTORY_SEPARATOR.'product'.DIRECTORY_SEPARATOR.
        $this->getidproduct().'.jpg')){

            $url = "/res/site/img/product/".$this->getidproduct().".jpg";
        }else{
            $url = "/res/site/img/product/embranco.jpg";
        }

        $this->setdesphoto($url);
        
    }

    public function getValues(){

        $this->checkPhone();
        $values = parent::getValues();
        return $values; 
    }  

    public function setPhoto($file){

        if(!empty($_FILES['file']["name"])){
        $extension = explode('.', $file['name']);
        $extension = end($extension);
        
        switch ($extension){

            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($file['tmp_name']);
            
            break;

            case 'gif':
                $image = imagecreatefromgif($file['tmp_name']);
            break;
            
            case 'png':
                $image = imagecreatefrompng($file['tmp_name']);
            break;    
        }

        $dist = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.
        'res'.DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.
        'img'.DIRECTORY_SEPARATOR.'product'.DIRECTORY_SEPARATOR.
        $this->getidproduct().'.jpg';

        imagejpeg($image, $dist);

        imagedestroy($image);

        $this->checkPhone();

        }else{return '';}

}


}