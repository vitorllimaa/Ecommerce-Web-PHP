<?php

namespace Map;

use Rain\Tpl;


class page{
    private $tpl;
    private $options = [];
    private $defaults = [
        "header"=>true,
        "footer"=>true,
        "data"=>[]
    ];
    //primeiro a ser executado!
    public function __construct($opts = array(), $tpl_dir = "/views/"){
        $this->options = array_merge($this->defaults, $opts);

        $config = array(
            "tpl_dir"       => $_SERVER["DOCUMENT_ROOT"].$tpl_dir,
            "cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
            "debug"         => false // set to false to improve the speed
           );

        Tpl::configure( $config );
        $this->tpl = new Tpl;

        $this->setData($this->options["data"]);
        //cabeçalho
        if($this->options["header"]=== true) $this->tpl->draw("header");

    }
    
    private function setData($data = array()){
       
        foreach($data as $key => $value){
            $this->tpl->assign($key, $value);
        }

    }

    public function setTpl($name, $data = array(), $returnHTML = false){
        $this->setData($data);
        return $this->tpl->draw($name, $returnHTML);
    }

    //ultimo a ser executado!
    public function __destruct(){
        //roda pé
        if($this->options["footer"]=== true) $this->tpl->draw("footer");        
    }



}


?>