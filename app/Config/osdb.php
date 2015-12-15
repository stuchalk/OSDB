<?php
// Store all project configuration parameters here

// Server
$config['server']=$_SERVER['SERVER_NAME'];
($config['server']=="sds.coas.unf.edu") ? $config['path']="/osdb" : $config['path']="";
($config['server']=="sds.coas.unf.edu") ? $config['protocol']="https" : $config['protocol']="http";
$config['url']=$config['protocol']."://".$config['server'].$config['path'];
$config['filepath']['jdx']="/files/jdx/";
$config['filepath']['xml']="/files/xml/";

// Defaults
$config['tech']['types']=['MS','IR','UVVIS','1HNMR','13CNMR'];
$config['index']['display']['cutoff']=20;

// Jmol
$config['jmol']['j2spath']=$config['path']."/js/jsmol/j2s";
$config['jmol']['proxy']=$config['url']."/jmol/proxy?url=";
$config['jmol']['color']="#FFFFFF";
$config['jmol']['height']=190;
$config['jmol']['width']=190;

// CIR
$config['cir']['url']="http://cactus.nci.nih.gov/chemical/structure/<id>/file?format=sdf&get3d=true";