<?php

class Script{
    public $path;
    public $type = "php";
    public $name;
    public function __construct($module_name, $script_name, $type = "php") {
        $this->name = $script_name;
        $this->path = CURRENT_WORKIN_DIR."/vendor/$module_name/scripts/$script_name";

        $this->type = $type;
    }

    public function run(){
        echo "Start script ".$this->name."\n";
        switch($this->type){
            case "php":
                $this->runPhp();
                break;
            case "bath":
                $this->runBath();
                break;
            default:
                echo "Unknown Type";
        }
    }

    public function runPhp(){
        include $this->path;
    }

    public function runBath(){

    }

    public function runSql(){

    }
}