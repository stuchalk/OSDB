<?php

/**
 * Class ChemicalsController
 */
class ChemicalsController extends AppController
{

    public $uses = ['Chemical'];

    /**
     * function beforeFilter
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('view');
    }

    /**
     * View a list of the chemicals
     */
    public function index()
    {
        $data = $this->Chemical->find('list', ['fields' => ['id', 'name', 'first'], 'order' => ['first', 'name']]);
        $this->set('data', $data);
    }

    /**
     * View a particular chemical
     * @param $id
     */
    public function view($id)
    {
        $data = $this->Chemical->find('first', ['conditions' => ['id' => $id]]);
        $this->set('data', $data);
    }

}