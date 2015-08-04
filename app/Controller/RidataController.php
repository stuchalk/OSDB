<?php

/**
 * Class RidataController
 * Controller for Refractive Index data
 */
class RidataController extends AppController {

	public $uses=['Ridata','Reference','Chemical'];

    /**
     * View RI data
     */
	public function view()
	{
		$data=$this->Ridata->find('first',['conditions'=>['Ridata.id'=>1]]);
		$this->set('data',$data);
	}
}