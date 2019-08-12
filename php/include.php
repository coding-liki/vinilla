<?php

define("SETTINGS_FILE", "vinilla.json");
define("TMP_DIR", "/tmp/vinilla_install_temp");
define("SERVER_URL", "http://vinillaserver.vinylcoding.ru");
define("MODULE_NOT_INSTALLED", 0);
define("MODULE_INSTALLED_AND_VINILLA", 1);
define("MODULE_INSTALLED_NOT_VINILLA", 2);
define("CURRENT_WORKIN_DIR", getcwd());

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// copies files and non-empty directories
function rcopy($src, $dst) {
    if (file_exists($dst)) rrmdir($dst);
    if (is_dir($src)) {
        mkdir($dst);
        $files = scandir($src);
        foreach ($files as $file)
        if ($file != "." && $file != "..") rcopy("$src/$file", "$dst/$file"); 
    }
    else if (file_exists($src)) copy($src, $dst);
}


/* 
* This function copy $source directory and all files 
* and sub directories to $destination folder
*/

function recursive_copy($src,$dst) {
	$dir = opendir($src);
	@mkdir($dst);
	while(( $file = readdir($dir)) ) {
		if (( $file != '.' ) && ( $file != '..' )) {
			if ( is_dir($src . '/' . $file) ) {
                //$this->recursive_copy($src .'/'. $file, $dst .'/'. $file);
                recursive_copy($src .'/'. $file, $dst .'/'. $file);
			}
			else {
                if (strpos($src .'/'. $file, '.git') === FALSE) {
                    echo $src .'/'. $file;
                    echo " to ";
                    echo $dst .'/'. $file."\n";
                }
                
				copy($src .'/'. $file,$dst .'/'. $file);
			}
		}
	}
	closedir($dir);
}


function deleteDir($src) { 
    // $dir_root = 
    $dir_path = explode("/", $src);
    if(!in_array($dir_path[1], ["tmp", "vendor"]) && !in_array($dir_path[0], ["tmp", "vendor"])){
        echo "trying to delete not tmp folder `$src`\n";
        exit(1);
    }
    $dir = opendir($src);
    while(false !== ( $file = readdir($dir)) ) { 
        if (( $file != '.' ) && ( $file != '..' )) { 
            if ( is_dir($src . '/' . $file) ) { 
                deleteDir($src . '/' . $file); 
            }
            else { 
                unlink($src . '/' . $file); 
            }
        }
    }
    closedir($dir); 
    rmdir($src);

}

function checkCreateFolder($folder, $mode = 0777){
    if(!is_dir($folder)){
        mkdir($folder, $mode, true);
        echo "Create folder\n";
        return false;
    }

    return true;
}

/**
 * Проверяем и создаём, если нет, папку vendor в текущей папке
 *
 * @return void
 */
function checkRootPath(){
    checkCreateFolder(CURRENT_WORKIN_DIR."/vendor");
}

function checkTmpFolder(){
    if(checkCreateFolder(TMP_DIR)){
        echo TMP_DIR." already exists\n";
    }
}

function checkVendorFolder($vendor, $work_folder){
    echo "current dir = `$work_folder`";
    checkCreateFolder($work_folder.'/vendor/'.$vendor);
    return $work_folder.'/vendor/'.$vendor;
}

function gitFetchModule($module_url, $folder){
    $cwd = getcwd();
    chdir($folder);
    
    $module_name = explode("/", $module_url);
    $module_name = $module_name[count($module_name) - 1];
    $module_name = trim(str_replace(".git", "",$module_name ));
    if(is_dir("./$module_name")){
        chdir($cwd);
        return $module_name;
    }
    $output = "";
    $clone_result = 0;
    exec("git clone $module_url", $output, $clone_result);
    if($clone_result != 0){
        echo "Fetching error\n";
        exit(1);
    }
    if(!is_dir("./$module_name")){
        echo "Fetching error\n";
        exit(1);
    }
    chdir($cwd);
    return $module_name;
}

function guessModuleUrl($module_url){
    if(strpos($module_url,"http") === 0){
        return $module_url;
    }

    $module_name_mass = explode("/", $module_url);

  
    if(count($module_name_mass) == 2){
        $vendor = $module_name_mass[0];
        $module_name = $module_name_mass[1];
        if(isset($cache[$vendor]) && isset($cache[$vendor][$module_name]) && isset($cache[$vendor][$module_name]['repo_url'])){
            return $cache[$vendor][$module_name]['repo_url'];
        }
        return "";
    }
}
require_once __DIR__."/Lib/Module.php";

require_once __DIR__."/Lib/Cache.php";

Cache::loadCache();