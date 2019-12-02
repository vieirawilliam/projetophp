<?php

namespace Hcode\Model;

use Exception;
use \Hcode\DB\Sql;
use \Hcode\Model;

class Category extends Model
{
    
    public static function listAll()
    {
        $sql = new Sql();

        return  $sql->select("Select * from tb_categories order by descategory");
    }
    public function save()
    {

        $sql = new Sql();

        $results = $sql->select("call sp_categories_save(:idcategory,:descategory)", array(         
            ":idcategory" =>$this->getidcategory(),
            ":descategory" =>$this->getdescategory()
        ));

        $this->setData($results[0]);
    }
    public function get($idcategory)
    {
        $sql = new Sql();

        $results = $sql->select(
            "Select * from tb_categories a where a.idcategory=:idcategory",
            array(
                ":idcategory" => $idcategory
            )
        );

        $this->setData($results[0]);
    }
    public function update()
    {
        $sql = new Sql();

        $results = $sql->select("call sp_usersupdate_save(:iduser,:desperson,:deslogin,:despassword,:desemail,:nrphone,:inadmin)", array(
            ":iduser" => strtoupper($this->getiduser()),
            ":desperson" => strtoupper($this->getdesperson()),
            ":deslogin" => strtoupper($this->getdeslogin()),
            ":despassword" => "",
            ":desemail" => strtoupper($this->getdesemail()),
            ":nrphone" => strtoupper($this->getnrphone()),
            ":inadmin" => strtoupper($this->getinadmin())
        ));

        $this->setData($results[0]);
    }
    public function delete()
    {

        $sql = new Sql();

        $sql->query("DELETE FROM tb_categories where idcategory = :idcategory", [
            ":idcategory" => $this->getidcategory()
        ]);
    }
    

}
