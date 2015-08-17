<?php

/**
 * Class TechniquesController
 */
class TechniquesController extends AppController
{
    public $uses=['Technique','Report'];

    /**
     * beforeFilter function
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow();
    }

    /**
     * View a property type
     * @param integer $id
     */
    public function view($id)
    {
        $data=$this->Technique->find('first',['conditions'=>['Technique.id'=>$id],'recursive'=>4]);
        $this->set('data',$data);
    }

    /**
     * List the properties
     */
    public function index()
    {
        $data=$this->Technique->find('list',['fields'=>['id','type'],'order'=>['type']]);
        $this->set('data',$data);
    }

}