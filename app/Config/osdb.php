<?php
// Store all project configuration parameters here
$config['server']=$_SERVER['SERVER_NAME'];
($config['server']=="sds.coas.unf.edu") ? $config['path']="/osdb" : $config['path']="";
$config['url']="https://".$config['server'].$config['path'];
$config['filepath']['jdx']="/files/jdx/";
$config['filepath']['xml']="/files/xml/";

// Jmol
$config['jmol']['j2spath']=$config['path']."/js/jsmol/j2s";
$config['jmol']['proxy']=$config['url']."/jmol/proxy?url=";
$config['jmol']['color']="#FFFFFF";
$config['jmol']['height']=190;
$config['jmol']['width']=190;

// CIR
$config['cir']['url']="http://cactus.nci.nih.gov/chemical/structure/<id>/file?format=sdf&get3d=True";