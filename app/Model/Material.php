<?php

App::uses('AppModel', 'Model');
class Material extends AppModel {
	
	public $useTable=false;

	public function total()
	{
		return 5;
	}
}
?>