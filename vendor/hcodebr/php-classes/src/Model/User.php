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
    const ERROR = "UserError";
    const ERROR_REGISTER = "UserErrorRegister";
    const SUCCESS = "UserSucesss";

    public static function getFromSession()
    {
        $user = new User();
        if (isset($_SESSION[User::SESSION]) && (int) $_SESSION[User::SESSION]['iduser'] > 0) {
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
            !(int) $_SESSION[User::SESSION]["iduser"] > 0
        ) {
            //Não está logado
            return false;
        } else {
            if ($inadmin === true && (bool) $_SESSION[User::SESSION]['inadmin'] === true) {
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

        $results = $sql->select("select t.*, p.desperson, p.desemail, p.nrphone from tb_users as t inner join tb_persons as p using(idperson) where deslogin = :LOGIN", array(
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

        return  $sql->select("Select *, b.desperson from tb_users a inner join tb_persons b using(idperson) order by b.desperson");
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
            ":inadmin" => strtoupper($this->getinadmin())
        ));

        $this->setData($results[0]);
    }
    public function get($iduser)
    {
        $sql = new Sql();

        $results = $sql->select(
            "Select *, b.desperson from tb_users a inner join tb_persons b using(idperson) where a.iduser=:iduser",
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

    public static function getAddress($idperson){
        $sql = new Sql();

        $results = $sql->select(
            "  SELECT * FROM tb_addresses as a 
               inner join tb_persons as u using(idperson) where idperson = :idperson order by idaddress desc limit 1 ",
            
            array(':idperson' =>  $idperson));

        if (count($results) === 0) {
            return $data["idaddress"] = 0 ;
        } else {
            $data = $results[0];
            return $data["idaddress"];
        }  
    }

    public static function getForgot($email, $inadmin = true)
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

                if ($inadmin === true) {
                    $link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";
                } else {
                    $link = "http://www.hcodecommerce.com.br/forgot/reset?code=$code";
                }
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
    public static function validForgotDecrypt($code)
    {

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
            ":idrecovery" => $idrecovery
        ));

        if (count($results) === 0) {
            throw new \Exception("Não foi possivél encontrar o usuário");
        } else {
            return $results[0];
        }
    }
    public static function  setForgotUsed($idrecovery)
    {

        $sql = new Sql();

        $sql->query(
            "Update tb_userspasswordsrecoveries set dtrecovery = now() where idrecovery = :idrecovery",
            array(
                ":idrecovery" => $idrecovery
            )
        );
    }
    public function setPassword($password)
    {

        $sql = new Sql();

        $sql->query("Update tb_users set despassword =:password where iduser = :iduser", array(
            ":password" => $password,
            ":iduser" => $this->getiduser()
        ));
    }
    public static function setError($msg)
    {
        $_SESSION[User::ERROR] = $msg;
    }
    public static function getError()
    {
        $msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : '';
        User::clearError();
        return $msg;
    }
    public static function clearError()
    {
        $_SESSION[User::ERROR] = NULL;
    }

    public static function setErrorRegister($msg)
    {
        $_SESSION[User::ERROR_REGISTER] = $msg;
    }
    public static function getErrorRegister()
    {
        $msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : '';
        User::clearErrorRegister();
        return $msg;
    }
    public static function clearErrorRegister()
    {
        $_SESSION[User::ERROR_REGISTER] = NULL;
    }
    public static function checkLoginExist($email)
    {
        $sql = new Sql();
        $results = $sql->select("SELECT p.*, u.* FROM db_ecommerce.tb_persons as p
            inner join db_ecommerce.tb_users as u using(idperson) where p.desemail = :desemail", [
            ':desemail' => $email
        ]);
        return (count($results) > 0);
    }
    public static function setSuccess($msg)
    {
        $_SESSION[User::SUCCESS] = $msg;
    }
    public static function getSuccess()
    {
        $msg = (isset($_SESSION[User::SUCCESS]) && $_SESSION[User::SUCCESS]) ? $_SESSION[User::SUCCESS] : '';
        User::clearSuccess();
        return $msg;
    }
    public static function clearSuccess()
    {
        $_SESSION[User::SUCCESS] = NULL;
    }
    public function getOrders()
	{
		$sql = new Sql();
		$results = $sql->select("
			SELECT * 
			FROM tb_orders a 
			INNER JOIN tb_ordersstatus b USING(idstatus) 
			INNER JOIN tb_carts c USING(idcart)
			INNER JOIN tb_users d ON d.iduser = a.iduser
			INNER JOIN tb_addresses e USING(idaddress)
			INNER JOIN tb_persons f ON f.idperson = d.idperson
			WHERE a.iduser = :iduser
		", [
			':iduser'=>$this->getiduser()
		]);
		return $results;
	}
}
