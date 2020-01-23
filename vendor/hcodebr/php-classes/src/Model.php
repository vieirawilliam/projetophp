<?php

namespace Hcode;

class Model
{

    private $values = [];

    public function __call($name, $args)
    {
        $method = substr($name, 0, 3);
        $fieldname = substr($name, 3, strlen($name));


        switch ($method) {

            case "get":

             
                if($fieldname === "despassword"){
                    return (isset($this->values[$fieldname]))? $this->values[$fieldname]: NULL;                    
                }else{
                    return (isset($this->values[$fieldname]))? strtoupper($this->values[$fieldname]): NULL;
                }
            
                break;

            case "set":
                $this->values[$fieldname] = $args[0];
                break;
        }
    }
    public function setData($data = array())
    {
        foreach ($data as $key => $value) {
            
            if (is_array($value))
            {
                $this->{"set" .$key}($value);
            }else{
                if($key === "despassword"){
                    $this->{"set" .$key}($value);
                }else{
                    $this->{"set" .$key}(strtoupper($value));
                }
            }
        }
    }
    public function getValues()
    {
        return $this->values;
    }
}
