<?php

class Cache{
    public static $modules;
    public static $nameIndex;
    public static $fullNameIndex;
    public static $urlIndex;

    public static function post($url, $data){
        $ch = curl_init();
    
        curl_setopt($ch, CURLOPT_URL, SERVER_URL.$url);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        $server_output = curl_exec($ch);
        curl_close ($ch);
        return $server_output;
    }

    public static function updateCache(){
        $cache_folder = __DIR__."/../cache";
        echo "Start updating\n";
        checkCreateFolder($cache_folder);
        $last_cache_id = "1";
        if (file_exists($cache_folder."/last_cache_id")) {
            $last_cache_id = file_get_contents($cache_folder."/last_cache_id");
        }
        echo "last cache id = $last_cache_id\n";
        $server_output = self::post("/cache/get_all", [ "cache_id"=> $last_cache_id]);
        $data = json_decode($server_output, true);
        if($data['result'] == "new"){
            
            $new_id = $data['new_id'];
            echo "Has new update\nid = $new_id\n";
            $server_output = self::post("/cache/get_all", [ "cache_id"=> $new_id, "force" => true]);
            
            file_put_contents($cache_folder."/$new_id", $server_output);
            file_put_contents($cache_folder."/last_cache_id", $new_id);
        } else {
            echo "Cache is up to date!!!\n";
        }
    }
    
    public static function loadCache(){
        static $cache_json = null;

        echo "Loading Cache\n";
        if($cache_json != null){
            return $cache_json;
        }
        $cache_folder = __DIR__."/../cache";
        if(!file_exists($cache_folder."/last_cache_id")){
            return false;
        }

        $last_cache_id = file_get_contents($cache_folder."/last_cache_id");
        
        echo "lasst cache id = $last_cache_id\n";
        $gzdata = file_get_contents($cache_folder."/$last_cache_id");
    
        $uncompressed = gzuncompress($gzdata);
        $json = json_decode($uncompressed, true);
        // print_r($json);
        $json['index'] = [];
        $id = 0;
        self::$modules = [];
        self::$nameIndex = [];
        self::$fullNameIndex = [];
        self::$urlIndex = [];

        foreach($json as $v_key => $vendor) { 
            if($v_key == "index"){
                continue;
            }
            foreach ($vendor as $key => $module) {
                if(is_string($module)){
                    $module = json_decode($module, true);
                }
                $new_module = new Module($module);
                self::$modules[$id] = $new_module;
                self::$nameIndex[$new_module->name] = $new_module;
                self::$fullNameIndex[$new_module->getFullName()] = $new_module;
                self::$urlIndex[$new_module->url] = $new_module;
                $id++;
                $json['index'][$module['repo_url']] = $module['vendor']."/".$module['name'];
            }
        }

        // print_r(self::$modules);
        $cache_json = $json;
        return $json;
    }
}