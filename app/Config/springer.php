<?php
// Store all project configuration parameters here

$config['server']="https://chalk.coas.unf.edu";
$config['path']="/springer";
$config['filepath']="/files/pdf/";
$config['pdftotextPath']['mac']=DS.'opt'.DS.'local'.DS.'bin'.DS.'pdftotext';
$config['pdftotextPath']['freebsd']=DS.'usr'.DS.'local'.DS.'bin'.DS.'pdftotext';
$config['pdftotextPath']['linux']='pdftotext';
$config['pdftotextPath']['windows']=WWW_ROOT.'exec'.DS.'pdftotext'.DS.'pdftotext.exe';
$config['textReplacementArray']=["–"=>"-"]; //array of replacement characters for text cleanup
