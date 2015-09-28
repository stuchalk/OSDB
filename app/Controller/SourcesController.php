<?php

/**
 * Class SourcesController
 */
class SourcesController extends AppController
{

    public $uses = ['Source'];

    /**
     * function beforeFilter
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow();
    }

    /**
     * View a list of the collections
     */
    public function index()
    {
        $data = $this->Source->find('list',['fields'=>['id','name'],'order'=>['name']]);
        $this->set('data', $data);
    }

    /**
     * View a particular collection
     * @param $id
     */
    public function view($id)
    {
        $data = $this->Source->find('first', ['conditions' => ['Collection.id' => $id]]);
        $this->set('data', $data);
    }

}