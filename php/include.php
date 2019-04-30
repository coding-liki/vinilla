<?php

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
                echo $src .'/'. $file;
                echo " to ";
                echo $dst .'/'. $file."\n";
				copy($src .'/'. $file,$dst .'/'. $file);
			}
		}
	}
	closedir($dir);
}


function deleteDir($src) { 
    // $dir_root = 
    if(!in_array(explode("/", $src)[1], ["tmp", "vendor"])){
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

function checkCreateFolder($folder){
    if(!is_dir($folder)){
        mkdir($folder);
        return false;
    }

    return true;
}