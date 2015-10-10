<?php
/**
 * Created by PhpStorm.
 * User: n00002621
 * Date: 5/28/15
 * Time: 9:59 AM
 */

/**
 * Class SystemsController
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
        $contain=['Substance'=>['fields'=>['name'],
                        'Identifier'=>['fields'=>['type','value']]],
                    'Context'=>['fields'=>['id'],
                        'Dataset'=>['fields'=>['id'],
                            'Propertytype']]];
        $data=$this->System->find('first',['conditions'=>['System.id'=>$id],'contain'=>$contain,'recursive'=> 3]);
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

    /**
     * Testing function
     */
    public function findsys()
    {
        $sid1="00001";$sid2="";
        debug($sid1);debug($sid2);
        $sarray=[$sid1];
        if($sid2!="") { $sarray[]=$sid2; }
        $data=$this->SubstancesSystem->findUnique($sarray);
        debug($data);exit;
    }

}