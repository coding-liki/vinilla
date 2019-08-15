<?php


require_once __DIR__."/include.php";



/**
 * Устанавливаем модуль с помощью git
 *
 * @param [type] $module_url
 * @return void
 */
function installModule($module_url, $updating=false, $check_tmp=true){
   
    $module = Cache::$fullNameIndex[$module_url] ?? Cache::$urlIndex[$module_url] ??  new Module($module_url);
    
    if(!$module->initialised){
        echo "Module is not known!\nTry to update Cache\n vinilla_php update\n";
        exit(1);
    }

    // if ($check_tmp) {
    //     checkTmpFolder();
    // }
    
    /** Проверим зависимости */
    $dependencies = $module->getDependencies();
    foreach($dependencies as $dependency){
        installModule($dependency, $updating, false);
    }

    if($module->isInstalled()){
        echo "Module `".$module->getFullName()."` is allredy installed\n";
        $module->loadLocalVersion();
        if($module->local_version != $module->settings['version']){
            echo "Module `".$module->getFullName()."` can be updated\nRun 'vinilla_php update ".$module->getFullName()."'\n";
        }
        return ;
    }

    echo "Installing `".$module->getFullName()."`\n";

    $current_working_dir = CURRENT_WORKIN_DIR;

    
    $cwd = getcwd();
    chdir(TMP_DIR);
    
    if(is_dir("./$module->git_name")){
        chdir($module->git_name);
        $pull_result = exec("git pull");
    } else {
        gitFetchModule($module->url,TMP_DIR);
        chdir("./$module->git_name");
    }

    
    
    if(!is_file("./".SETTINGS_FILE)){
        echo "Not Vinilla module\n";
        exit(1);
    }

    // $settings = json_decode(file_get_contents("./".SETTINGS_FILE), true);
    // print_r($settings);
    
    
    
    $vendor = $module->vendor;

    if($vendor == ""){
        echo "vendor is not set in module settings\n";
        exit(1);
    }
    
    $install_module_name = $module->name;

    $vendor_dir = checkVendorFolder($vendor, CURRENT_WORKIN_DIR);

    chdir($vendor_dir);

    $old_settings = [];
    if(checkCreateFolder("./$install_module_name")){
        chdir("./$install_module_name");
        if ( is_file("./".SETTINGS_FILE) ) {
            $old_settings = json_decode(file_get_contents("./".SETTINGS_FILE), true);
        } else {
            echo "Was Not Vinilla module\nReinstall as Vinilla module (Y/N)?\n";
            $answer = readline();
            if(in_array( $answer, ["N", "n", "н", "Н", "No", "no", "Нет", "нет"])){
                exit(1);
            }          
        }
    }

    $module_name = $module->git_name;
    if (!isset($old_settings['version']) || ($old_settings['version'] < $settings['version'] && $updating)) {
        echo "copying modules files from '".TMP_DIR."/$module_name' to '$vendor_dir/$install_module_name'\n";
        recursive_copy(TMP_DIR."/$module_name", "$vendor_dir/$install_module_name");
        echo "copy complete!\n\n";
    } else if($old_settings['version'] < $module->settings['version']) {
        echo "module can be updated\nPlease run \n**********************\nvinilla_php update $vendor/$install_module_name\n**********************\n";
    } else {
        echo "You have the newest version of $vendor/$install_module_name\n";
    }
    chdir($cwd);
}

function uninstallModule($module_name){
    checkRootPath();
    $module = Cache::$fullNameIndex[$module_name] ?? Cache::$urlIndex[$module_name] ??  new Module($module_name);
    echo $module->getFullName()." uninstalling\n";
    if($module->isInstalled()){
        deleteDir("./vendor/".$module->getFullName());
    } else {
        echo "module is not installed!!!\nPlease run \n**********************\nvinilla_php install ".$module->getFullName()."\n**********************\n";
    }
}

function updateModule($module_name){
    checkRootPath();
    $module = Cache::$fullNameIndex[$module_name] ?? Cache::$urlIndex[$module_name] ??  new Module($module_name);
    if ($module->isInstalled()) {
        if (is_file("./vendor/".$module->getFullName()."/".SETTINGS_FILE)) {
            chdir("./vendor/".$module->getFullName());
            exec("git pull");
        } else {
            echo "It is not Vinilla module!!!\nPlease run \n**********************\nvinilla_php install ".$module->getFullName()."\n**********************\n";
        }
    } else {
        echo "module is not installed!!!\nPlease run \n**********************\nvinilla_php install ".$module->getFullName()."\n**********************\n";
    }
}


function clearVendors(){
    deleteDir("./vendor");
    echo "Deleting all vendors\n";
}


function selfUpdate(){
    $interpretator = "php";
    $install_dir = str_replace("/php","",__DIR__);
    chdir(TMP_DIR);
    gitFetchModule("https://github.com/coding-liki/vinilla.git", "./");
    chdir("vinilla");
    exec("./install.sh -t $interpretator -f $install_dir");
    echo "UPdated successfully";
}
// $longopts  = array(
//     "install",     // Обязательное значение
//     "uninstall",
    
// );
// $options = getopt("", $longopts);
checkTmpFolder();
$command = ""; 
$one_commands = [
    'update',
    'load',
    'clear',
    "self-update"
];
if($argc > 1){
    $command = $argv[1];
}
if($argc <3 && !in_array($command, $one_commands)){
    echo "You need to specify command(install/uninstall) and module url\n";
    exit(1);
}
for ($i=2; $i<$argc;$i++) {
    switch ($command) {
        case "install":
            installModule($argv[$i]);
            break;
        case "uninstall":
            uninstallModule($argv[$i]);
            break;
        case "update":
            updateModule($argv[$i]);
            break;
        case "getcache":
            Cache::updateCache();
            break;
        default:
            echo "Используй либо install либо uninstall либо update";
    }
}
if($argc <3){
    switch($command) {
        case "update":
            Cache::updateCache();
            break;
        case "self-update":
            selfUpdate();
            break;
        case "clear":
            clearVendors();
            break;
        case "load":
            Cache::loadCache();
            break;
    }
}
// print_r($options);
