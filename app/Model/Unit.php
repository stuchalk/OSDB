<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Unit
 * Unit model
 */
class Unit extends AppModel
{

	public $hasAndBelongsToMany = ['Quantity', 'Parameter','Variable'];

}
?>