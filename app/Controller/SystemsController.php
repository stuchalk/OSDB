<?php
/**
 * Created by PhpStorm.
 * User: n00002621
 * Date: 5/28/15
 * Time: 9:59 AM
 */

class SystemsController extends AppController {

    public $uses=['System','SubstancesSystem'];

    /**
     * beforeFilter function
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow();
    }

    /**
     * View a system
     */
    public function view($id)
    {
        $data=$this->System->find('first',['conditions'=>['System.id'=>$id],'recursive'=>3]);
        $this->set('data',$data);
    }

    /**
     * View index of systems
     */
    public function index()
    {
        $data=$this->System->find('all');
        $this->set('data',$data);
    }

    public function findsys()
    {
        $subid1="00001";$subid2="00003";
        debug($subid1);debug($subid2);
        $data=$this->SubstancesSystem->findUnique([$subid1,$subid2]);
        debug($data);exit;
    }

}