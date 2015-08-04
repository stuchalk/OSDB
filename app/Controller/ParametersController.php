<?php

/**
 * Class ParametersController
 * Actions related to dealing with parameters
 * @author Stuart Chalk <schalk@unf.edu>
 *
 */
class ParametersController extends AppController
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
        $data=$this->Parameter->find('list',['fields'=>['Parameter.id','base'],'order'=>['base']]);
        //echo "<pre>";print_r($data);echo "</pre>";exit;
        $this->set('data',$data);
    }

    /**
     * View a quantity
     * @param $id
     */
    public function view($id)
    {
        $data=$this->Parameter->find('first',['conditions'=>['Parameter.id'=>$id],'recursive'=>3]);
        echo "<pre>";print_r($data);echo "</pre>";exit;
        $this->set('data',$data);
    }

}