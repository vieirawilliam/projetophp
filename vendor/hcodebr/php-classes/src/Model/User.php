<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Funcoes\Funcoes;
use Hcode\Mailer;
use \Hcode\Model;


class User extends Model
{

    const SESSION = "User";
    const SECRET = "HcodePhp7_Secret";
    const SECRET_IV = "HcodePhp7_Secret_IV";

    public static function getFromSession()
	{
		$user = new User();
		if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0) {
			$user->setData($_SESSION[User::SESSION]);
		}
		return $user;
    }
    public static function checkLogin($inadmin = true)
	{
		if (
			!isset($_SESSION[User::SESSION])
			||
			!$_SESSION[User::SESSION]
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0
		) {
			//Não está logado
			return false;
		} else {
			if ($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true) {
				return true;
			} else if ($inadmin === false) {
				return true;
			} else {
				return false;
			}
		}
	}
    public static function login($login, $password)
    {

        $sql = new Sql();
        $descSenha = new Funcoes();

        $results = $sql->select("select * from tb_users where deslogin = :LOGIN", array(
            ":LOGIN" => $login
        ));

        if (count($results) === 0) {
            throw new \Exception("Usário inválida.");
        }

        $data = $results[0];
        $senha = $descSenha->decodIF($data["despassword"]);

        if ($password === $senha) {
            $user = new User();

            $user->setData($data);

            $_SESSION[User::SESSION] = $user->getValues();

            return $user;
        } else {
            throw new \Exception("Senha invalida");
        }
    }
    public static function verifyLogin($inadmin = true)
    {

        if (!User::checkLogin($inadmin)) {
			if ($inadmin) {
				header("Location: /admin/login");
			} else {
				header("Location: /login");
			}
			exit;
		}
    }
    public static function logout()
    {

        $_SESSION[User::SESSION] = NULL;
    }
    public static function listAll()
    {
        $sql = new Sql();

        return  $sql->select("Select * from tb_users a inner join tb_persons b using(idperson) order by b.desperson");
    }
    public function save()
    {

        $sql = new Sql();

        $codif =  new Funcoes();

        $password = $codif->codIF(strtoupper($this->getdespassword()));

        $results = $sql->select("call sp_users_save(:desperson,:deslogin,:despassword,:desemail,:nrphone,:inadmin)", array(
            ":desperson" => strtoupper($this->getdesperson()),
            ":deslogin" => strtoupper($this->getdeslogin()),
            ":despassword" => $password,
            ":desemail" => strtoupper($this->getdesemail()),
            ":nrphone" => strtoupper($this->getnrphone()),
            ":inadmin" =>strtoupper( $this->getinadmin())
        ));

        $this->setData($results[0]);
    }
    public function get($iduser)
    {
        $sql = new Sql();

        $results = $sql->select(
            "Select * from tb_users a inner join tb_persons b using(idperson) where a.iduser=:iduser",
            array(
                ":iduser" => $iduser
            )
        );

        $this->setData($results[0]);
    }
    public function update()
    {
        $sql = new Sql();

        $codif =  new Funcoes();

        $password = $codif->codIF(strtoupper($this->getdespassword()));

        $results = $sql->select("call sp_usersupdate_save(:iduser,:desperson,:deslogin,:despassword,:desemail,:nrphone,:inadmin)", array(
            ":iduser" => strtoupper($this->getiduser()),
            ":desperson" => strtoupper($this->getdesperson()),
            ":deslogin" => strtoupper($this->getdeslogin()),
            ":despassword" => $password,
            ":desemail" => strtoupper($this->getdesemail()),
            ":nrphone" => strtoupper($this->getnrphone()),
            ":inadmin" => strtoupper($this->getinadmin())
        ));

        $this->setData($results[0]);
    }
    public function delete()
    {

        $sql = new Sql();

        $sql->query("call sp_users_delete(:iduser)", array(
            ":iduser" => $this->getiduser()
        ));
    }
    public static function getForgot($email)
    {

        $sql = new Sql();

        $results = $sql->select(
            "
                   select * from tb_persons a
                   inner join tb_users b using(idperson) 
                   where a.desemail = :email",
            array(':email' => $email)
        );

        if (count($results) === 0) {
            throw new \Exception("Não possível recuperar a senha");
        } else {

            $data = $results[0];

            $results2 = $sql->select(
                "CALL sp_userspasswordsrecoveries_create(:iduser, :desip)",
                array(
                    ":iduser" => $data["iduser"],
                    ":desip" => $_SERVER["REMOTE_ADDR"]
                )
            );

            if (count($results2) === 0) {
                throw new \Exception("Não possível recuperar a senha");
            } else {
                $dataRecovery = $results2[0];

                $code = openssl_encrypt($dataRecovery['idrecovery'], 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));
                $code = base64_encode($code);

                $link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";

                $mailer = new Mailer(
                    $data["desemail"],
                    $data["desperson"],
                    "Redefinir Senha da Hcode Store",
                    "forgot",
                    array(
                        "name" => $data["desperson"],
                        "link" => $link
                    )
                );

                $mailer->send();

                return $data;
            }
        }
    }
    public static function validForgotDecrypt($code){
        
        $code = base64_decode($code);
		$idrecovery = openssl_decrypt($code, 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

        $sql = new Sql();

        $results = $sql->select("
			SELECT *
			FROM tb_userspasswordsrecoveries a
			INNER JOIN tb_users b USING(iduser)
			INNER JOIN tb_persons c USING(idperson)
			WHERE
				a.idrecovery = :idrecovery
				AND
				a.dtrecovery IS NULL
				AND
				DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
		", array(
			":idrecovery"=>$idrecovery
        ));
        
        if (count($results) === 0){
            throw new \Exception("Não foi possivél encontrar o usuário");
        }else{
            return $results[0];    
        }
        
    }
    public static function  setForgotUsed($idrecovery){

        $sql = new Sql();

        $sql->query("Update tb_userspasswordsrecoveries set dtrecovery = now() where idrecovery = :idrecovery",
        array(
            ":idrecovery"=>$idrecovery
        ));

    }
    public function setPassword($password){

        $sql = new Sql();

        $sql->query("Update tb_users set despassword =:password where iduser = :iduser",array(
            ":password"=>$password,
            ":iduser"=>$this->getiduser()
        ));
    }

}
