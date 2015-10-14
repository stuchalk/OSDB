<?php
// Store all project configuration parameters here
$config['server']=$_SERVER['SERVER_NAME'];
($config['server']=="sds.coas.unf.edu") ? $config['path']="/osdb" : $config['path']="";
$config['url']="https://".$config['server'].$config['path'];
$config['filepath']['jdx']="/files/jdx/";
$config['filepath']['xml']="/files/xml/";