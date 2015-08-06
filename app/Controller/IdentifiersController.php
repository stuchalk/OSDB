<?php

/**
 * Class IdentifiersController
 * Actions related to dealing with identifiers
 * @author Stuart Chalk <schalk@unf.edu>
 *
 */
class IdentifiersController extends AppController
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
        $data=$this->Identifier->find('list',['fields'=>['Parameter.id','base'],'order'=>['base']]);
        //echo "<pre>";print_r($data);echo "</pre>";exit;
        $this->set('data',$data);
    }

    /**
     * View a quantity
     * @param $id
     */
    public function view($id)
    {
        $data=$this->Identifier->find('first',['conditions'=>['Identifier.id'=>$id],'recursive'=>1]);
        //echo "<pre>";print_r($data);echo "</pre>";exit;
        $this->set('data',$data);
    }

    public function search($term)
    {
        $data=$this->Identifier->find('all',['fields'=>['DISTINCT Identifier.substance_id','Substance.name'],'order'=>['Substance.name'],'conditions'=>['Identifier.value like'=>'%'.$term.'%'],'recursive'=>1]);
        $this->set('data',$data);
        $this->render('view');
    }
}