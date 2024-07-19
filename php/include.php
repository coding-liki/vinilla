<?php

use Lib\Cache;

const SETTINGS_FILE = "vinilla.json";
const BINS_CONFIG_FILE = 'bins.json';
const TMP_DIR = "/tmp/vinilla_install_temp";
const SERVER_URL = "vinillaserver.vinylcoding.ru";
const MODULE_NOT_INSTALLED = 0;
const MODULE_INSTALLED_AND_VINILLA = 1;
const MODULE_INSTALLED_NOT_VINILLA = 2;
const VINILLA_INSTALLATION_DIR = __DIR__;

define("CURRENT_WORKING_DIR", getcwd());

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/*
* This function copy $source directory and all files
* and sub directories to $destination folder
*/

function recursive_copy($src, $dst): void
{
    $dir = opendir($src);
    if (!@mkdir($dst) && !is_dir($dst)) {
        throw new RuntimeException(sprintf('Directory "%s" was not created', $dst));
    }
    while (($file = readdir($dir))) {
        if (($file !== '.') && ($file !== '..')) {
            if (is_dir($src . '/' . $file)) {
                recursive_copy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

function deleteDir($src): void
{
    if (patchCanBeDeleted($src)) {
        echo "trying to delete not project and not tmp folder `$src`\n";
        exit(1);
    }
    $dir = opendir($src);
    while (false !== ($file = readdir($dir))) {
        if (($file !== '.') && ($file !== '..')) {
            if (is_dir($src . '/' . $file)) {
                deleteDir($src . '/' . $file);
            } else {
                echo "Delete $src/$file\n";
                unlink($src . '/' . $file);
            }
        }
    }
    closedir($dir);
    rmdir($src);

}

/**
 * @param string $src
 * @return bool
 */
function patchCanBeDeleted(string $src): bool
{
    $dir_path = explode("/", $src);

    $availableStarting = ["tmp", "vendor", basename(CURRENT_WORKING_DIR)];

    return !in_array($dir_path[1], $availableStarting, true)
        && !in_array($dir_path[0], $availableStarting, true);
}

function checkCreateFolder(string $folder, int $mode = 0777): bool
{
    if (!is_dir($folder)) {
        if (!@mkdir($folder, $mode, true) && !is_dir($folder)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $folder));
        }
        return false;
    }

    return true;
}

/**
 * Проверяем и создаём, если нет, папку vendor в текущей папке
 *
 * @return void
 */
function checkRootPath(): void
{
    checkCreateFolder(CURRENT_WORKING_DIR . "/vendor");
}

function checkTmpFolder(): void
{
    checkCreateFolder(TMP_DIR);
}

function checkVendorFolder($vendor, $work_folder): string
{
    checkCreateFolder($work_folder . '/vendor/' . $vendor);
    return $work_folder . '/vendor/' . $vendor;
}

function gitFetchModule($module_url, $folder)
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
    $module_url = guessModuleUrl($module_url);
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

function guessModuleUrl($module_url)
{
    if (strpos($module_url, "http") === 0) {
        return $module_url;
    }

    $module_name_mass = explode("/", $module_url);


    if (count($module_name_mass) === 2) {
        list($vendor, $module_name) = $module_name_mass;

        return $cache[$vendor][$module_name]['repo_url'] ?? "";
    }

    return "";
}


Cache::loadCache();
