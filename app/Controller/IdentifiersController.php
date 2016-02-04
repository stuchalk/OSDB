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
        $this->Auth->allow('index','view','test');
    }

    /**
     * List the quantities
     */
    public function index()
    {
        $data=$this->Identifier->find('list',['fields'=>['Parameter.id','base'],'order'=>['base']]);
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

    /**
     * Search for a substance using one of its identifiers
     * @param $term
     */
    public function search($term)
    {
        $data=$this->Identifier->find('all',['fields'=>['DISTINCT Identifier.substance_id','Substance.name'],'order'=>['Substance.name'],'conditions'=>['Identifier.value like'=>'%'.$term.'%'],'recursive'=>1]);
        $this->set('data',$data);
        $this->render('view');
    }

    /**
     * Temporary function to test adding Wikidata codes to substances
     * @param $name
     */
    public function test($name)
    {
        $sub=$this->Identifier->find('first',['fields'=>['id','substance_id'],'conditions'=>['value'=>$name]]);
        if(!empty($sub)) {
            $sid=$sub['Identifier']['substance_id'];
            $resp=$this->Identifier->find('first',['fields'=>['id','value'],'conditions'=>['substance_id'=>$sid,'type'=>'inchikey']]);
            $key=$resp['Identifier']['value'];
            debug($key);
            $data=$this->Identifier->getWikidataId($sub['Identifier']['substance_id'],$key);
            debug($data);exit;
        } else {
            exit;
        }

    }
}