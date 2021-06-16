<?php
namespace Map\model;

use Map\DB\Sql;
use Map\Mailer;
use Exception;
use Map\Model;
use Map\Model\User;

class Cart extends Model {

    const SESSION = 'Cart';

    public static function getFromSession(){

        $cart = new Cart();

        if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0 ){

            $cart->get((int)$_SESSION[Cart::SESSION]['idcart']);
        }else{

            $cart = new Cart();
            $resultado = [
                'idcart'=>0
            ];
            $cart->setData($resultado); 
            $cart->getFromSessionID();

            if(!(int)$cart->getidcart() > 0){

                $data = [
                    'dessessionid'=>session_id()
                ];
                if(User::checkLogin(false)){
                    $user = User::getFromSession();

                    $data['iduser'] = $user->getiduser();
                }
                $cart->setData($data);
                $cart->setToSession();
                $cart->save();
                
                
            }
        }
    }

    public function setToSession(){

        $_SESSION[Cart::SESSION] = $this->getValues();
    }

    public function getFromSessionID(){

        $sql = new Sql();

        $result = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid",[
            ':dessessionid'=>session_id()
        ]);

        if(count($result) > 0){
            $this->setData($result[0]);
        }
    }


    public function get(int $idcart){

        $sql = new Sql();

        $result = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart",[
            ':idcart'=>$idcart
        ]);

        if(count($result) > 0){
            $this->setData($result[0]);
        }
        
    }

    public function save(){

        $sql = new Sql();
        $result = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays, :dtregister)", array(
            ":idcart"=>$this->getidcart(),
            ":dessessionid"=>$this->getdessessionid(),
            ":iduser"=>$this->getiduser(),
            ":deszipcode"=>$this->getdeszipcode(),
            ":vlfreight"=>$this->getvlfreight(),
            ":nrdays"=>$this->getnrdays(),
            ":dtregister"=>date('Y-m-d H:i:s')
        ));

        if(count($result) > 0){
            $this->setData($result[0]);
        }
    }

}

?>