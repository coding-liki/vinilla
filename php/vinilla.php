<?php
define("SETTINGS_FILE", "vinilla.json");
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

function deleteDir($src) { 
    if(explode("/", $src)[1] != "tmp"){
        echo "trying to delete not tmp folder";
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

/**
 * Проверяем и создаём, если нет, папку vendor в текущей папке
 *
 * @return void
 */
function checkRootPath(){
    checkCreateFolder("./vendor");
}

function checkTmpFolder(){
    if(checkCreateFolder("/tmp/vinilla_install_temp")){
        echo "we have tmp folder so deleting it\n";
        deleteDir("/tmp/vinilla_install_temp/");
        checkTmpFolder();
    }
}

function checkVendorFolder($vendor, $work_folder){
    checkCreateFolder($work_folder.'/vendor/'.$vendor);
    return $work_folder.'/vendor/'.$vendor;
}

function checkCreateFolder($folder){
    if(!is_dir($folder)){
        mkdir($folder);
        return false;
    }

    return true;
}
/**
 * Устанавливаем модуль с помощью git
 *
 * @param [type] $module_url
 * @return void
 */
function installModule($module_url, $check_tmp=true){
    if ($check_tmp) {
        checkRootPath();
    }

    echo "$module_url installing\n";

    $current_working_dir = getcwd();

    if ($check_tmp) {
          checkTmpFolder();
    }
    chdir("/tmp/vinilla_install_temp");

    $clone_result = exec("git clone $module_url");
    
    $module_name = explode("/", $module_url);
    $module_name = $module_name[count($module_name) - 1];
    $module_name = trim(str_replace(".git", "",$module_name ));
    
    echo "module name = $module_name\n";
    
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
    $vendor = $settings['vendor'] ?? "";

    if($vendor == ""){
        echo "vendor is not set in module settings\n";
        exit(1);
    }
    
    $module_name = $settings['module_name'] ?? $module_name;

    $vendor_dir = checkVendorFolder($vendor, $current_working_dir);

    chdir($vendor_dir);

    if(checkCreateFolder("./$module_name")){
        chdir("./$module_name");
        if ( is_file("./".SETTINGS_FILE) ) {
            $old_settings = json_decode(file_get_contents("./".SETTINGS_FILE), true);
            
        }

    }

    chdir($current_working_dir);
}

function uninstallModule($module_name){
    checkRootPath();
    echo "$module_name uninstalling\n";
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
switch($command){
    case "install":
        installModule($argv[2]);
        break;
    case "uninstall":
        uninstallModule($argv[2]);
        break;
    default:
        echo "Используй либо install либо uninstall";
}

// print_r($options);

echo "$command goood\n";
