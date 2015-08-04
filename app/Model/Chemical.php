<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Chemical
 * Chemical model
 */
class Chemical extends AppModel
{
	
	public $hasMany = array('ridata');

    public $virtualFields=['first'=>"UPPER(SUBSTR(TRIM(LEADING '123456789' from Chemical.name),1,1))"];

}