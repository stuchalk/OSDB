<?php
App::uses('Controller', 'Controller');
App::uses('HttpSocket','Network/Http');
App::uses('Xml','Utility');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');
App::uses('PhpReader', 'Configure');

Configure::config('default', new PhpReader());
Configure::load('springer','default');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {

    public $components = ['Utils','Email','RequestHandler','Session',
                            'Auth' => ['loginRedirect' => ['controller' => 'pages','action' => 'display','home'],
                                    'logoutRedirect' => ['controller' => 'pages','action' => 'display','home']]];
    public $helpers = ['Form','Html','Session','Time'];

}
