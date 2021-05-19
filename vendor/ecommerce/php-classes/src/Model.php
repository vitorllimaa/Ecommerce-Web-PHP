<?php
namespace Map;

class Model {

    private $values = [];

    public function __call($name, $args)
    {
        $method = substr($name,0, 3);
        $fielName = substr($name,3, strlen($name));
        
        switch ($method)
        {
            case "get":
                return $this->values[$fielName];
                break;  
            case "set":
                $this->values[$fielName] = $args[0];
                break;  
        } 
    }
     public function setData ($data = array()){

        foreach ($data as $key => $value){

            $this->{"set".$key}($value);

        }
    } 

    public function getValues (){

        return $this->values;
    }

}


?>