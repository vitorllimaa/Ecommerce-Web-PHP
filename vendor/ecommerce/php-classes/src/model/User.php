<?php
namespace Map\model;

use Map\DB\Sql;
use Map\Mailer;
use Exception;
use Map\Model;

class User extends Model {
    const SESSION = "User";
    const SECRET = "Maiscommerce2021";
    const SECRET_IV = "Maiscommerce2021_IV";
    public static function login($login, $password){
    
        $sql = new Sql();
        $result = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
            //segunça para login no html
            ":LOGIN"=>$login
        ));
        //existe login no banco
        if(count($result) === 0)
        {
            var_export(json_encode($result));
            throw new \Exception("Usuário ou senha não encontrado.");
        }
        //trazer o primeiro resultado do banco
        $data = $result[0]; 
        $Data = password_hash($data["despassword"], PASSWORD_DEFAULT);

        if(password_verify($password, $Data) === true){
            $user = new User();
            $user->setData($data);

            $_SESSION[User::SESSION] =  $user->getValues();
            
            return $user;

        }else{
            throw new \Exception("Usuário ou senha não encontrado."); 
        }
    }

    public static function verifyLogin($adm = true){

        if(
            !isset($_SESSION[User::SESSION])
            ||
            !$_SESSION[User::SESSION]
            ||
            (bool)$_SESSION[User::SESSION]["inadmin"] !== $adm
        ) {
            header("Location: /admin/login");
            exit;
        }

    }
    public static function logout(){
        $_SESSION[User::SESSION] = NULL;
    }

    public static function listAll(){

        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) order by desperson");

    }

    public function save(){

        $sql = new Sql();

        $result = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":desperson"=>$this->getname(),
            ":deslogin"=>$this->getlogin(),
            ":nrphone"=>$this->gettelefone(),
            ":desemail"=>$this->getemail(),
            ":despassword"=>$this->getpassword(),
            ":inadmin"=>$this->getinadmin()
        ));
       
    }

    public function get($id_login){

        $sql = new Sql();

        $result = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
            ":iduser"=>$id_login
        ));
        $this->setData($result[0]);
    }

    public function update($user){

        $sql = new Sql();
        $result =$sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)",array(
            ":iduser"=>$this->getiduser(),
            ":desperson"=>$user["name"],
            ":deslogin"=>$user["login"],
            ":despassword"=>$this->getdespassword(),
            ":desemail"=>$user["email"],
            ":nrphone"=>$user["telefone"],
            ":inadmin"=>$user["inadmin"],
        ));
        $this->setData($result[0]);

    }

    public function delete($id_login){
        $sql = new Sql();

        $sql->select("CALL sp_users_delete(:iduser)", array(
            ":iduser"=>$id_login
        ));
        

    }
    public static function getforgot($email){
        $sql = new Sql();

        $result = $sql->select("SELECT * FROM tb_persons a INNER JOIN tb_users b WHERE a.desemail = :email", array(
            ":email"=>$email
        ));

        if(count($result) === 0 ){
            throw new \Exception("Email não encontrado!"); 
        }else{
            $data = $result[0];

            $result2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
                ":iduser"=>$data["iduser"],
                ":desip"=>$_SERVER["REMOTE_ADDR"]
            ));

            if(count($result2) === 0){
                throw new \Exception("Email não encontrado!"); 
            }else{

                $dataRecovery = $result2[0];
                
				$code = openssl_encrypt($dataRecovery['idrecovery'], 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

				$code = base64_encode($code);

                $link = "http://www.megamais.com.br/admin/forgot/reset?code=$code";

                $mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir senha!", "forgot",
                array(
                    "name"=>$data["desperson"],
                    "link"=>$link
                ));

                $mailer->send();
                
                return $data;
            }
        }

    }

     public static function validForgotDecrypt($result){

        $code = base64_decode($result);

        $idrecovery = openssl_decrypt($code, 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));
        $sql = new Sql();
        $result1 = $sql->select("SELECT * FROM tb_userspasswordsrecoveries a INNER JOIN tb_users b USING(iduser)
        INNER JOIN tb_persons c USING(idperson) WHERE a.idrecovery = :idrecovery and a.dtrecovery IS NULL 
        AND DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW()",array(
            ":idrecovery"=>$idrecovery
        ));

        if(count($result1) === 0 ){
            throw new \Exception("Não foi possível recuperar a senha!");
        }else{
            return $result1[0];
        }

    }
    
    public static function setForgotUser($id, $password){   
        $sql = new Sql();
        $result1 = $sql->query("UPDATE tb_users SET despassword = :despassword WHERE iduser = :iduser", array(
            ":despassword"=>$password,
            ":iduser"=>$id
        ));


    }
    public function nameUser(){
        $sql = new Sql();
        $result1 = $sql->select("SELECT * FROM tb_login WHERE senha = :password", array(
            ":password"=>$this->getpassword()
        ));

        return $result1[0];

    }
}

?>