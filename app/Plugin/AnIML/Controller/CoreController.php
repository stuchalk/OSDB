<?php

/**
 * Class CoreController
 */
class CoreController extends AnimlAppController {
	
	public $uses=['Animl.Core','Animl.Jcamp','Animl.Animl'];

	/**
	 * function beforeFilter
	 */
	public function beforeFilter()
	{
		parent::beforeFilter();
		$this->Auth->allow();
	}

	/**
	 * function createModels
	 * Takes the current AnIML core schema and creates Model files for elements, simpleTypes,
	 * ComplexTypes, and attributeGroups to be used in the creation of AnIML documents
	 */
	public function createModels()
	{
		$schema=$this->Animl->readCore();

		// Process each different defined part from the least to most complicated

		// SimpleTypes
		$this->Core->createComplex($schema['complexType']);
		$this->Core->createElement($schema['element']);
		$this->Core->createSimple($schema['simpleType']);
		$this->Core->createAttrgroup($schema['attributeGroup']);
		exit;

		//echo "<pre>";print_r($schema);echo "</pre>";exit;
	}
}