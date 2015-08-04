<?php
/**
 * Created by PhpStorm.
 * User: n00002621
 * Date: 5/28/15
 * Time: 9:59 AM
 */

class SystemsController extends AppController {

    public $uses=['System'];

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


}