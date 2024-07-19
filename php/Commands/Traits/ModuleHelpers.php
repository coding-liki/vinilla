<?php

namespace Commands\Traits;

use Lib\Cache;
use Lib\Module;

trait ModuleHelpers
{
    use FoldersHelper;

    public function checkAndInstallDependencies(Module $module): void
    {
        $dependencies = $module->getDependencies();
        foreach ($dependencies as $dependency) {
            $this->installModule($dependency);
        }
    }

    public function installModule($module_url, $updating = false): void
    {
        static $depth = 0;
        $depth++;
        $module = Cache::$fullNameIndex[$module_url] ?? Cache::$urlIndex[$module_url] ?? new Module($module_url);

        if (!$module->initialised) {
            echo "Module is not known!\nTry to update Cache\n vinilla_php update\n";
            exit(1);
        }

        /** Проверим зависимости */
        $dependencies = $module->getDependencies();
        foreach ($dependencies as $dependency) {
            $this->installModule($dependency, $updating);
        }

        if ($module->isInstalled()) {
            echo "Module `" . $module->getFullName() . "` is already installed\n";
            $module->loadLocalVersion();
            if ($module->local_version !== $module->settings['version']) {
                echo "Module `" . $module->getFullName() . "` can be updated\nRun 'vinilla_php update " . $module->getFullName() . "'\n";
            }

            return;
        }

        echo "Installing `" . $module->getFullName() . "`\n";

        $current_working_dir = CURRENT_WORKING_DIR;

        $cwd = getcwd();
        chdir(TMP_DIR);

        if (is_dir("./$module->git_name")) {
            chdir($module->git_name);
            exec("git pull");
        } else {
            $this->gitFetchModule($module->url, TMP_DIR);
            chdir("./$module->git_name");
        }

        if (!is_file("./" . SETTINGS_FILE)) {
            echo "Not Vinilla module\n";
            exit(1);
        }

        $vendor = $module->vendor;

        if ($vendor === "") {
            echo "vendor is not set in module settings\n";
            exit(1);
        }

        $install_module_name = $module->name;

        $vendor_dir = $this->checkVendorFolder($vendor, CURRENT_WORKING_DIR);

        chdir($vendor_dir);

        $old_settings = [];
        if ($this->checkCreateFolder("./$install_module_name")) {
            chdir("./$install_module_name");
            if (is_file("./" . SETTINGS_FILE)) {
                $old_settings = json_decode(file_get_contents("./" . SETTINGS_FILE), true);
            } else {
                echo "Was Not Vinilla module\nReinstall as Vinilla module (Y/N)?\n";
                $answer = readline();
                if (in_array($answer, ["N", "n", "н", "Н", "No", "no", "Нет", "нет"])) {
                    exit(1);
                }
            }
        }

        $module_name = $module->git_name;
        if (!isset($old_settings['version']) || ($old_settings['version'] < $module->settings['version'] && $updating)) {
            $this->recursive_copy(TMP_DIR . "/$module_name", "$vendor_dir/$install_module_name");
            echo "copy complete!\n\n";
            $module->runScripts();

            $this->updateProjectDependencies($current_working_dir, [$module->getFullName()]);
        } else if ($old_settings['version'] < $module->settings['version']) {
            echo "module can be updated\nPlease run \n**********************\nvinilla_php update $vendor/$install_module_name\n**********************\n";
        } else {
            echo "You have the newest version of $vendor/$install_module_name\n";
        }
        if ($depth === 1) {
            $this->postInstallProcess();
        }
        chdir($cwd);
    }

    public function gitFetchModule($module_url, $folder): string
    {
        $cwd = getcwd();
        chdir($folder);

        $module_name = explode("/", $module_url);
        $module_name = $module_name[count($module_name) - 1];
        $module_name = trim(str_replace(".git", "", $module_name));
        if (is_dir("./$module_name")) {
            chdir("./$module_name");
            exec("git pull");
            chdir($cwd);
            return $module_name;
        }
        $output = "";
        $clone_result = 0;
        $module_url = $this->guessModuleUrl($module_url);
        exec("git clone $module_url &>/dev/null", $output, $clone_result);
        if ($clone_result != 0) {
            echo "Fetching error\n";
            exit(1);
        }
        if (!is_dir("./$module_name")) {
            echo "Fetching error\n";
            exit(1);
        }
        chdir($cwd);
        return $module_name;
    }

    public function guessModuleUrl($module_url): string
    {
        if (str_starts_with($module_url, "http")) {
            return $module_url;
        }

        $module_name_mass = explode("/", $module_url);


        if (count($module_name_mass) === 2) {
            list($vendor, $module_name) = $module_name_mass;

            return $cache[$vendor][$module_name]['repo_url'] ?? "";
        }

        return "";
    }


    public function updateProjectDependencies(string $current_working_dir, array $addDependencies = [], array $removeDependencies = []): void
    {
        $theCwd = getcwd();
        chdir($current_working_dir);
        $settings = file_get_contents(SETTINGS_FILE);
        $project = new Module(json_decode($settings, true));
        $dependencies = $project->getDependencies();
        if (!empty($addDependencies)) {
            array_push($dependencies, ...$addDependencies);
        }

        $dependencies = array_values(array_unique(array_diff($dependencies, $removeDependencies)));
        $project->setDependencies($dependencies);
        $this->saveSettings($project->settings);
        chdir($theCwd);
    }

    public function saveSettings(array $settings): void
    {
        file_put_contents(SETTINGS_FILE, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public function postInstallProcess(): void
    {
        chdir(CURRENT_WORKING_DIR);
        $settings = file_get_contents(SETTINGS_FILE);
        $rootModule = new Module(json_decode($settings, true));
        $this->runPostInstallDependencyScripts($rootModule);
        $this->updateBinsSettings($rootModule);
    }

    public function updateBinsSettings(Module $module): array
    {
        static $depth = 0;
        $depth++;

        $currentDepth = $depth;

        $bins = $module->bins;
        foreach ($module->getDependencies() as $dependency) {
            $moduleDependency = Cache::$fullNameIndex[$dependency] ?? Cache::$urlIndex[$dependency] ?? new Module($dependency);
            $bins += $this->updateBinsSettings($moduleDependency);
        }

        if ($currentDepth > 1) {
            return $bins;
        }

        $this->checkCreateFolder(BINS_FOLDER);
        file_put_contents(BINS_FOLDER . BINS_JSON_FILE_NAME, json_encode($bins));

        return [];
    }

    public function runPostInstallDependencyScripts(Module $module): void
    {
        foreach ($module->getDependencies() as $dependency) {
            $moduleDependency = Cache::$fullNameIndex[$dependency] ?? Cache::$urlIndex[$dependency] ?? new Module($dependency);
            if (isset($moduleDependency->settings['after_full_install_script'])) {
                $scriptPath = CURRENT_WORKING_DIR . "/vendor/" . $dependency . '/' . $moduleDependency->settings['after_full_install_script'];
                if (str_ends_with($scriptPath, '.php')) {
                    echo "Start script $dependency/{$moduleDependency->settings['after_full_install_script']}\n";
                    include_once $scriptPath;
                }
            }
        }
    }


    public function updateModule(string $module_name): void
    {
        $this->checkRootPath();
        $module = Cache::$fullNameIndex[$module_name] ?? Cache::$urlIndex[$module_name] ?? new Module($module_name);
        if ($module->isInstalled()) {
            if (is_file("./vendor/" . $module->getFullName() . "/" . SETTINGS_FILE)) {
                chdir("./vendor/" . $module->getFullName());
                exec("git pull");
            } else {
                echo "It is not Vinilla module!!!\nPlease run \n**********************\nvinilla_php install " . $module->getFullName() . "\n**********************\n";
            }
        } else {
            echo "module is not installed!!!\nPlease run \n**********************\nvinilla_php install " . $module->getFullName() . "\n**********************\n";
        }
    }
}