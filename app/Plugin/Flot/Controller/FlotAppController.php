<?php

App::uses('PhpReader', 'Configure');

Configure::config('default', new PhpReader());
Configure::load('Flot.flot','default');

class FlotAppController extends AppController {
	
	// Needed for URL access to plugin

}

?>