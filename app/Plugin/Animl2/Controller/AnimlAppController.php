<?php

App::uses('PhpReader', 'Configure');

Configure::config('default', new PhpReader());
Configure::load('Animl.animl','default');
// TODO: The config files are not loading correctly...

/**
 * Class AnimlAppController
 */
class AnimlAppController extends AppController {
	
	// Needed for URL access to plugin

}