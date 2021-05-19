<?php
namespace Map;
use Map\pageadmin;

class login extends pageadmin {
    public function __construct($opts = array(), $tpl_dir ="/views/login/")
    {
        parent::__construct($opts, $tpl_dir);
    }

}
?>