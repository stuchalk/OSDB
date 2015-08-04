<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Setting
 * Setting model
 */
class Setting extends AppModel
{

    public $belongsTo = ['Dataset'];

}