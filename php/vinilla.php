<?php
define("SETTINGS_FILE", "vinilla.json");
define("TMP_DIR", "/tmp/vinilla_install_temp");
define("SERVER_URL", "http://vinillaserver.vinylcoding.ru");
define("MODULE_NOT_INSTALLED", 0);
define("MODULE_INSTALLED_AND_VINILLA", 1);
define("MODULE_INSTALLED_NOT_VINILLA", 2);
define("CURRENT_WORKIN_DIR", getcwd());

require_once __DIR__."/include.php";
/**
 * Проверяем и создаём, если нет, папку vendor в текущей папке
 *
 * @return void
 */
function checkRootPath(){
    checkCreateFolder("./vendor");
}

function checkTmpFolder(){
    if(checkCreateFolder(TMP_DIR)){
        // echo "we have tmp folder so deleting it\n";
        // deleteDir(TMP_DIR);
        // checkTmpFolder();
        echo TMP_DIR." already exists\n";
    }
}

function checkVendorFolder($vendor, $work_folder){
    checkCreateFolder($work_folder.'/vendor/'.$vendor);
    return $work_folder.'/vendor/'.$vendor;
}

function gitFetchModuleToTmp($module_url){
    chdir(TMP_DIR);
    $module_name = explode("/", $module_url);
    $module_name = $module_name[count($module_name) - 1];
    $module_name = trim(str_replace(".git", "",$module_name ));
    if(is_dir("./$module_name")){
        deleteDir("./$module_name");
    }
    $clone_result = exec("git clone $module_url");

    if(!is_dir("./$module_name")){
        echo "Fetching error\n";
        exit(1);
    }
}

/**
 * Устанавливаем модуль с помощью git
 *
 * @param [type] $module_url
 * @return void
 */
function installModule($module_url, $updating=false, $check_tmp=true){
    if ($check_tmp) {
        checkRootPath();
    }
    $module_name = explode("/", $module_url);
    $module_name = $module_name[count($module_name) - 1];
    $module_name = trim(str_replace(".git", "",$module_name ));
    
    echo "module name = $module_name\n";
    // $is_installed = ifModuleInstalled()
    echo "$module_url installing\n";

    $current_working_dir = CURRENT_WORKIN_DIR;

    if ($check_tmp) {
        checkTmpFolder();
    }
    chdir(TMP_DIR);
    
    
    
    if(is_dir("./$module_name")){
        deleteDir("./$module_name");
    }
    $clone_result = exec("git clone $module_url");

    if(!is_dir("./$module_name")){
        echo "Fetching error\n";
        exit(1);
    }

    chdir("./$module_name");
    
    if(!is_file("./".SETTINGS_FILE)){
        echo "Not Vinilla module\n";
        exit(1);
    }

    $settings = json_decode(file_get_contents("./".SETTINGS_FILE), true);
    print_r($settings);
    
    if(array_key_exists('require', $settings)){
        echo "Has dependings!!!";

        $dependings = $settings['require'];

        foreach($dependings as $depending){
            installModule($depending);
        } 
    }
    
    $vendor = $settings['vendor'] ?? "";

    if($vendor == ""){
        echo "vendor is not set in module settings\n";
        exit(1);
    }
    
    $install_module_name = $settings['module_name'] ?? $module_name;

    $vendor_dir = checkVendorFolder($vendor, $current_working_dir);

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

    if (!isset($old_settings['version']) || ($old_settings['version'] < $settings['version'] && $updating)) {
        echo "copying modules files from '".TMP_DIR."/$module_name' to '$vendor_dir/$install_module_name'\n";
        recursive_copy(TMP_DIR."/$module_name", "$vendor_dir/$install_module_name");
        echo "copy complete!\n\n";
    } else if($old_settings['version'] < $settings['version']) {
        echo "module can be updated\nPlease run \n**********************\nvinilla_php update $vendor/$install_module_name\n**********************\n";
    } else {
        echo "You have the newest version of $vendor/$install_module_name\n";
    }
    chdir($current_working_dir);

    
}

function ifModuleInstalled($module_name_with_vendor){
    $current_working_dir = CURRENT_WORKIN_DIR;
    if (is_dir("./vendor/$module_name_with_vendor")) {
        if(is_file("./vendor/$module_name_with_vendor/".SETTINGS_FILE)){
            return MODULE_INSTALLED_AND_VINILLA;
        } else {
            return MODULE_INSTALLED_NOT_VINILLA;
        }
    } else {
        return MODULE_NOT_INSTALLED;
    }
}
function uninstallModule($module_name){
    checkRootPath();
    echo "$module_name uninstalling\n";
    if(is_dir("./vendor/$module_name")){
        deleteDir("./vendor/$module_name");
    } else {
        echo "module is not installed!!!\nPlease run \n**********************\nvinilla_php install $module_name\n**********************\n";
    }
}

function updateModule($module_name){
    checkRootPath();
    if (is_dir("./vendor/$module_name")) {
        if (is_file("./vendor/$module_name/".SETTINGS_FILE)) {
            $old_settings = json_decode(file_get_contents("./vendor/$module_name/".SETTINGS_FILE), true);
            if (isset($old_settings['repo_url'])) {
            } else {
                chdir("./vendor/$module_name");
                exec("git pull");
            }
        } else {
            echo "It is not Vinilla module!!!\nPlease run \n**********************\nvinilla_php install $module_name\n**********************\n";
        }
    } else {
        echo "module is not installed!!!\nPlease run \n**********************\nvinilla_php install $module_name\n**********************\n";
    }
}

function updateCache(){
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, SERVER_URL."/cache/get_all");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $server_output = curl_exec($ch);
    echo "cache_json = $server_output\n";
    curl_close ($ch);

}
// $longopts  = array(
//     "install",     // Обязательное значение
//     "uninstall",
    
// );
// $options = getopt("", $longopts);

$command = ""; 
if($argc > 1){
    $command = $argv[1];
}
if($argc <3){
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
        default:
            echo "Используй либо install либо uninstall либо update";
    }
}
if($argc <3){
    switch($command) {
        case "update":
            updateCache();
            break;
    }
}
// print_r($options);
