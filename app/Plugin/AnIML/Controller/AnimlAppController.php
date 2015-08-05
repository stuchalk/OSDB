<?php

App::uses('PhpReader', 'Configure');

Configure::config('default', new PhpReader());
Configure::load('Animl.animl','default');
Configure::load('Animl.jcamp','default');

class AnimlAppController extends AppController {
	
	// Needed for URL access to plugin

}

?>