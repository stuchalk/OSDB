<?php

App::uses('PhpReader', 'Configure');

Configure::config('default', new PhpReader());
Configure::load('Jcamp.jcamp','default');
// TODO: The config files are not loading correctly...

class JcampAppController extends AppController {

    // Needed for URL access to plugin

}