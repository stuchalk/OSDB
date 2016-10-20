<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Isdbsample
 */
class Isdbsample extends AppModel
{
    public $useDbConfig = 'isdb';
    public $useTable = 'samples';
}