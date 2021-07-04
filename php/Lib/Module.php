<?php

class Module
{
    public $url = "";
    public $name = "";
    public $vendor = "";
    public $settings = [];
    public $git_name = "";
    public $initialised = false;
    public $local_version = "";
    public $scripts;

    public function __construct($settings)
    {
        if (is_string($settings)) {
            $this->init($settings);
            return;
        }
        $this->settings = $settings;

        $this->name = $settings['name'] ?? "";
        $this->vendor = $settings['vendor'] ?? "";
        $this->url = $settings['repo_url'] ?? "";
        $this->scripts = $settings['scripts'] ?? [];

        $git_module_name = explode("/", $this->url);
        $git_module_name = $git_module_name[count($git_module_name) - 1];
        $git_module_name = trim(str_replace(".git", "", $git_module_name));

        $this->git_name = $git_module_name;

        if ($this->name != "" && $this->vendor != "" && $this->url != "") {
            $this->initialised = true;
        }
    }

    public function init($module_url, $tmp_folder = TMP_DIR)
    {
        $git_module_name = gitFetchModule($module_url, $tmp_folder);
        if (is_file($tmp_folder . "/$git_module_name/" . SETTINGS_FILE)) {
            $module_settings = json_decode(file_get_contents($tmp_folder . "/$git_module_name/" . SETTINGS_FILE), true);

            $this->settings = $module_settings;

            $this->name = $module_settings['name'] ?? "";
            $this->vendor = $module_settings['vendor'] ?? "";
            $this->url = $module_settings['repo_url'] ?? "";
            $this->scripts = $module_settings['scripts'] ?? [];

            $this->git_name = $git_module_name;

            if ($this->name != "" && $this->vendor != "" && $this->url != "") {
                $this->initialised = true;
            }
        }
    }

    public function getFullName()
    {
        return $this->vendor . "/" . $this->name;
    }

    public function getDependencies()
    {
        if (isset($this->settings['depends_on'])) {
            return $this->settings['depends_on'];
        }

        if (isset($this->settings['dependencies'])) {
            return $this->settings['dependencies'];
        }

        return [];
    }

    public function setDependencies(array $dependencies)
    {
        if (isset($this->settings['depends_on'])) {
            $this->settings['depends_on'] = $dependencies;
        } elseif (isset($this->settings['dependencies'])) {
            $this->settings['dependencies'] = $dependencies;
        }
    }

    public function runScripts()
    {
        foreach ($this->scripts as $script) {
            echo "run script \n";
            print_r($script);
            $script_o = new Script($this->getFullName(), $script['name'], $script['type'] ?? "php");
            $script_o->run();
        }
    }

    public function isInstalled()
    {
        $cwd = getcwd();
        chdir(CURRENT_WORKIN_DIR);
        $full_name = $this->getFullName();
        if ($this->initialised && is_dir("./vendor/" . $full_name)) {
            if (is_file("./vendor/$full_name/" . SETTINGS_FILE)) {
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

    public function loadLocalVersion()
    {
        if ($this->isInstalled() === MODULE_INSTALLED_AND_VINILLA) {
            $cwd = getcwd();
            chdir(CURRENT_WORKIN_DIR);
            $local_settings = json_decode(file_get_contents("./vendor/" . $this->getFullName() . "/" . SETTINGS_FILE),
                true);
            $this->local_version = $local_settings['version'];
        } else {
            $this->local_version = $this->settings['version'] ?? "";
        }
    }
}
