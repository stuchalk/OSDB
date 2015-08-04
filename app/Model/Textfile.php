<?php

App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class TextFile
 * TextFile model
 * test
 */
class TextFile extends AppModel
{

    public $actsAs = ['Containable'];

    public $belongsTo = ['File'];
}
