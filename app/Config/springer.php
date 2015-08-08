<?php
// Store all project configuration parameters here

$config['server']="https://sds.coas.unf.edu";
$config['path']="/osds";
$config['filepath']['jdx']="/files/jdx/";
$config['filepath']['xml']="/files/xml/";
$config['pdftotextPath']['mac']=DS.'opt'.DS.'local'.DS.'bin'.DS.'pdftotext';
$config['pdftotextPath']['freebsd']=DS.'usr'.DS.'local'.DS.'bin'.DS.'pdftotext';
$config['pdftotextPath']['linux']='pdftotext';
$config['pdftotextPath']['windows']=WWW_ROOT.'exec'.DS.'pdftotext'.DS.'pdftotext.exe';
$config['textReplacementArray']=["–"=>"-"]; //array of replacement characters for text cleanup
