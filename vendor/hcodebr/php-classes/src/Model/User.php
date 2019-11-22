<?php

namespace Hcode\Model;

use Exception;
use \Hcode\DB\Sql;
use \Hcode\Funcoes\Funcoes;
use \Hcode\Model;

class User extends Model
{

    const SESSION = "User";

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

    public static function verifyLogin($inadmin = true){

        if(
            !isset($_SESSION[User::SESSION])
            ||
            !$_SESSION[User::SESSION]
            ||
            !(int)$_SESSION[User::SESSION]["iduser"] > 0
            ||
            (bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
            
        ){
          
            header("Location: /admin/login");
            exit;
        }
    }

    public static function logout()
	{

		$_SESSION[User::SESSION] = NULL;

	}

}
