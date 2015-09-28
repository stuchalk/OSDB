<?php

/**
 * Class TechController
 */

class TechController extends AnimlAppController {

    public $uses=['Animl.Tech','Animl.Jcamp','Animl.Animl'];

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
     * Takes the current AnIML technique schema and creates Model files for elements, simpleTypes,
     * and ComplexTypes to be used in the creation of AnIML documents
     */
    public function createModels()
    {
        $schema=$this->Animl->readTech();

        // Process each different defined part from the least to most complicated

        $this->Tech->createSimple($schema['simpleType']);
        $this->Tech->createElement($schema['element']);
        $this->Tech->createComplex($schema['complexType']);

        echo "<pre>";print_r($schema);echo "</pre>";exit;

    }

    public function access($atdd,$part)
    {
        $path=Configure::read('animl.schemapath').rawurlencode($atdd);
        $schema=$this->Animl->readAtdd($path);
        $opts=$schema[$part];
        $data=array();
        foreach($opts as $opt) { $data[]=$opt['@name']; }
        if($this->request->is('requested'))	{ return $data; }
    }
}

?>