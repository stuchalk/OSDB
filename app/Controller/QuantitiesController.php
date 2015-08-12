<?php

/**
 * Class QuantitiesController
 * Actions related to dealing with quantities
 * @author Stuart Chalk <schalk@unf.edu>
 */
class QuantitiesController extends AppController
{

    /**
     * function beforeFilter
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow();
    }

    /**
     * List the quantities
     */
    public function index()
    {
        $data=$this->Quantity->find('list',['fields'=>['Quantity.id','base'],'order'=>['base']]);
        $this->set('data',$data);
    }

    /**
     * View a quantity
     * @param $id
     */
    public function view($id)
    {
        $data=$this->Quantity->find('first',['conditions'=>['Quantity.id'=>$id],'recursive'=>3]);
        $this->set('data',$data);
    }
}