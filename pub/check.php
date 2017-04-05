<?php
$root = dirname(__DIR__);
$main = $root."/vendor/magento";

$directories = array();
function expandDirectories($base_dir ) {
     global $main;
     global $root;
  	foreach(scandir($base_dir) as $file) {

        if($file == '.' || $file == '..') continue;
        $dir = $base_dir.'/'.$file;
        if(is_dir($dir)) {
        	
            //$directories []= $dir;
            expandDirectories($dir);
           
        }else
        {
            
        	if (sha1_file($dir) != sha1_file(str_replace($main,$root."/fresh/vendor/magento",$dir))) {
				   echo $dir.'<br>';
			}
        	
        }
    }
     
}
expandDirectories($main);
print_r($directories);