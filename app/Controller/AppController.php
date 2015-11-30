<?php
App::uses('Controller', 'Controller');
App::uses('HttpSocket','Network/Http');
App::uses('Xml','Utility');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');
App::uses('PhpReader', 'Configure');

Configure::config('default', new PhpReader());
Configure::load('osdb','default');

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

    public $components = ['Export','Utils','Email','RequestHandler','Session', 'Paginator',
                            'Auth' => ['loginRedirect' => ['controller' => 'users','action' => 'dashboard'],
                                    'logoutRedirect' => ['controller' => 'pages','action' => 'display','home']]];
    public $helpers = ['Form','Html','Session','Time','Flash'];

    public $actsAs = ['Containable'];

}