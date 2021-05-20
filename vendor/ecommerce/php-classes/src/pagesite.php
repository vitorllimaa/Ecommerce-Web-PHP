<?php
namespace Map;
use Map\page;

class pagesite extends page {
    public function __construct($opts = array(), $tpl_dir ="/views/site/")
    {
        parent::__construct($opts, $tpl_dir);
    }

}
?>