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
            return $result;
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

    public function addProduct($product, $idCart){
        $idcart = (isset($idCart[0]["idcart"])) ? $idCart[0]["idcart"] : null;
        $sql = new Sql();
        $sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES(:idcart, :idproduct)",[
            ':idcart'=>$idcart,
            ':idproduct'=>$product->getidproduct()
        ]);
    }

    public function removeProduct(Product $product, $all = false){

        $sql = new Sql();
        
        if($all){
            $sql->query("UPDATE tb_cartsproducts SET dtremoved = now() WHERE idcart = :idcart AND idproduct = :idproduct AND
        dtremoved IS NULL",[
            ':idcart'=>$this->getFromSessionID()[0]["idcart"],
            ':idproduct'=>$product->getidproduct()
        ]);
        }else{
        $sql->query("UPDATE tb_cartsproducts SET dtremoved = now() WHERE idcart = :idcart AND idproduct = :idproduct AND
        dtremoved IS NULL LIMIT 1",[
            ':idcart'=>$this->getFromSessionID()[0]["idcart"],
            ':idproduct'=>$product->getidproduct()
        ]);
     }
    }

    public function getProduct($idCart){
        $idcart = (isset($idCart[0]["idcart"])) ? $idCart[0]["idcart"] : null;
        $sql = new Sql();

        $rows =  $sql->select("SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, 
        b.vlheight, b.vllength, b.vlweight, b.desurl, COUNT(*) AS nrqtd,
        SUM(b.vlprice) AS vltotal
        FROM tb_cartsproducts a 
        INNER JOIN tb_products b ON a.idproduct = b.idproduct 
        WHERE a.idcart = :idcart AND a.dtremoved IS NULL
        GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl
        ORDER BY b.desproduct", [
            ':idcart'=>$idcart
        ]);


        return Product::checkList($rows);
        
    }

    public function getProductsTotals(){

        $sql = new Sql();

        $session = $_SESSION["Cart"]["dessessionid"];

        $idcart = $sql->select("SELECT idcart FROM tb_carts WHERE dessessionid = :dessessionid", [
            ':dessessionid'=>$session
        ]);

        $result = $sql->select("SELECT SUM(vlprice) as vlprice, SUM(vlwidth) as vlwidth, SUM(vlheight) as vlheight, SUM(vllength) as vllength,
        SUM(vlweight) as vlweight, count(*) as nrqtd FROM tb_products a INNER JOIN tb_cartsproducts b ON a.idproduct = b.idproduct
        WHERE b.idcart = :idcart AND dtremoved IS NULL",[
            ':idcart'=>$idcart[0]["idcart"]
        ]);

        return $results = count($result) > 0 ? $result[0] : [];
    }

    public function setFreight($nrzipcode){

        $nrzipcode = str_replace("-", "", $nrzipcode);

        $totals = $this->getProductsTotals();

        if($totals > 0){

            $qs = http_build_query([
                'nCdEmpresa' => '',
                'sDsSenha' => '',
                'nCdServico' => '41106',
                'sCepOrigem' => '07174250',
                'sCepDestino' => $nrzipcode,
                'nVlPeso' => $totals['vlweight'],
                'nCdFormato' => '1',
                'nVlComprimento' => $totals['vllength'],
                'nVlAltura' => $totals['vlheight'],
                'nVlLargura' => $totals['vlwidth'],
                'nVlDiametro' => '0',
                'sCdMaoPropria' => 'S',
                'nVlValorDeclarado' => $totals['vlprice'],
                'sCdAvisoRecebimento' => 'S'
            ]);
        
            $xml = (array)simplexml_load_file(utf8_encode("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs));
            
            echo json_encode($xml);
            exit;
            
        } else {

        }
    }

}

?>