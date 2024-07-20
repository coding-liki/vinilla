<?php

namespace Lib;

class Module
{
    public string $url = "";
    public string $name = "";
    public string $vendor = "";
    public array $settings = [];
    public string $git_name = "";
    public bool $initialised = false;
    public string $local_version = "";
    public array $scripts = [];

    public array $bins = [];

    public function __construct(string|array $settings)
    {
        if (is_string($settings)) {
            $this->init($settings);
            return;
        }

        $this->parseSettings($settings);

        $git_module_name = explode("/", $this->url);
        $git_module_name = $git_module_name[count($git_module_name) - 1];
        $git_module_name = trim(str_replace(".git", "", $git_module_name));

        $this->git_name = $git_module_name;

        if ($this->name != "" && $this->vendor != "" && $this->url != "") {
            $this->initialised = true;
        }
    }

    public function parseSettings(array $settings): void
    {
        $this->settings = $settings;

        $this->name = $settings['name'] ?? "";
        $this->vendor = $settings['vendor'] ?? "";
        $this->url = $settings['repo_url'] ?? "";
        $this->scripts = $settings['scripts'] ?? [];
        $this->bins = $settings['bins'] ?? [];
    }

    public function init($module_url, $tmp_folder = TMP_DIR): void
    {
        $git_module_name = gitFetchModule($module_url, $tmp_folder);
        if (is_file($tmp_folder . "/$git_module_name/" . SETTINGS_FILE)) {
            $module_settings = json_decode(file_get_contents($tmp_folder . "/$git_module_name/" . SETTINGS_FILE), true);

            $this->parseSettings($module_settings);

            $this->git_name = $git_module_name;

            if ($this->name != "" && $this->vendor != "" && $this->url != "") {
                $this->initialised = true;
            }
        }
    }

    public function getFullName(): string
    {
        return $this->vendor . "/" . $this->name;
    }

    public function getDependencies(): array
    {
        return $this->settings['depends_on'] ?? $this->settings['dependencies'] ?? [];
    }

    public function setDependencies(array $dependencies): void
    {
        $dependenciesField = isset($this->settings['depends_on']) ? 'depends_on' : 'dependencies';

        $this->settings[$dependenciesField] = $dependencies;
    }

    public function runScripts(): void
    {
        foreach ($this->scripts as $script) {
            echo "run script: \n";
            print_r($script);
            $script_o = new Script($this->getFullName(), $script['name'], $script['type'] ?? "php");
            $script_o->run();
        }
    }

    public function isInstalled(): int
    {
        $cwd = getcwd();
        chdir(CURRENT_WORKING_DIR);
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

    public function loadLocalVersion(): void
    {
        if ($this->isInstalled() === MODULE_INSTALLED_AND_VINILLA) {
            $cwd = getcwd();
            chdir(CURRENT_WORKING_DIR);
            $local_settings = json_decode(file_get_contents("./vendor/" . $this->getFullName() . "/" . SETTINGS_FILE),
                true);
            $this->local_version = $local_settings['version'];
        } else {
            $this->local_version = $this->settings['version'] ?? "";
        }
    }
}
