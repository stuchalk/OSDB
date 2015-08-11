<?php

App::uses('PhpReader', 'Configure');

Configure::config('default', new PhpReader());
Configure::load('Animl.animl','default');
Configure::load('Animl.jcamp','default');
// TODO: The config files are not loading correctly...

class AnimlAppController extends AppController {
	
	// Needed for URL access to plugin

}

?>