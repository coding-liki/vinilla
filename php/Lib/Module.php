<?php
class Module{
    public $url = "";
    public $name = "";
    public $vendor = "";
    public $settings = [];
    public $git_name = "";
    public $initialised = false;
public $local_version = "";

    public function __construct($settings)
    {
        if(is_string($settings)){
            $this->init($settings);
            return;
        } 
        $this->settings = $settings;

        $this->name =$settings['name'] ?? "";
        $this->vendor =$settings['vendor'] ?? "";
        $this->url =$settings['repo_url'] ?? "";

        $git_module_name = explode("/", $this->url);
        $git_module_name = $git_module_name[count($git_module_name) - 1];
        $git_module_name = trim(str_replace(".git", "",$git_module_name ));
        
        $this->git_name = $git_module_name;

        if($this->name != "" && $this->vendor != "" && $this->url != "" ){
            $this->initialised = true;
        }
    }

    public function init($module_url){
        $git_module_name = gitFetchModule($module_url, TMP_DIR);
        if(is_file(TMP_DIR."/$git_module_name/".SETTINGS_FILE)){
            $module_settings = json_decode(file_get_contents(TMP_DIR."/$git_module_name/".SETTINGS_FILE), true);

            $this->settings = $module_settings;

            $this->name =$module_settings['name'] ?? "";
            $this->vendor =$module_settings['vendor'] ?? "";
            $this->url =$module_settings['repo_url'] ?? "";
            $this->git_name = $git_module_name;

            if($this->name != "" && $this->vendor != "" && $this->url != "" ){
                $this->initialised = true;
            }
        }
    }

    public function getFullName(){
        return $this->vendor."/".$this->name;
    }

    public function getDependencies(){
        if(isset($this->settings['depends_on']) )
            return $this->settings['depends_on'];
        
        if(isset($this->settings['dependencies']) ){
            return $this->settings['dependencies'];
        }

        return [];
    }

    public function isInstalled(){
        $cwd = getcwd();
        chdir(CURRENT_WORKIN_DIR);
        $full_name = $this->getFullName();
        if ($this->initialised && is_dir("./vendor/".$full_name)) {
            if(is_file("./vendor/$full_name/".SETTINGS_FILE)){
                chdir($cwd);
                return MODULE_INSTALLED_AND_VINILLA;
            } else {
                chdir($cwd);
                return MODULE_INSTALLED_NOT_VINILLA;
            }
        } else {
            chdir($cwd);
            return MODULE_NOT_INSTALLED;
        }
    }

    public function loadLocalVersion(){
        if($this->isInstalled() == MODULE_INSTALLED_AND_VINILLA){
            $cwd = getcwd();
            chdir(CURRENT_WORKIN_DIR);
            $local_settings = json_decode(file_get_contents("./vendor/".$this->getFullName()."/".SETTINGS_FILE), true);
            $this->local_version = $local_settings['version'];
        } else {
            $this->local_version = $this->settings['version'] ?? "";
        }
    }
}