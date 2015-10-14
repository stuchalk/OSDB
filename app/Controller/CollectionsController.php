<?php

/**
 * Class CollectionsController
 */
class CollectionsController extends AppController
{

    public $uses = ['Collection','Report'];

    /**
     * function beforeFilter
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('check');
    }

    /**
     * View a list of the collections (all or for a user)
     * @param integer $uid
     */
    public function index($uid=null)
    {
        if(is_null($uid)) {
            $data = $this->Collection->find('list',['fields'=>['id','name','first'],'order'=>['first','name']]);
        } else {
            $data = $this->Collection->find('list',['fields'=>['id','name','first'],'order'=>['first','name'],'conditions'=>['user_id'=>$uid]]);
        }
        $this->set('data', $data);
    }

    /**
     * Add a collection
     */
    public function add()
    {
        if($this->request->is('post')) {
            //debug($this->request->data);exit;
            $this->Collection->add($this->request->data['Collection']);
            $this->redirect('/users/dashboard');
        } else {
            $data = $this->Collection->find('list',['fields'=>['name'],'order'=>['name']]);
            $this->set('data',$data);
        }
    }

    /**
     * View a particular collection
     * @param $id
     */
    public function view($id)
    {
        $data = $this->Collection->find('first', ['conditions' => ['Collection.id' => $id]]);
        $this->set('data', $data);
        $cols=$this->Report->bySubstance('col',$id);
        $this->set('cols',$cols);
    }

    /**
     * Check the value of field (jQuery)
     * @param string $field
     * @param string $value
     */
    public function check($field="",$value="")
    {
        $c = $this->Collection->find('count',['fields'=>[$field],'conditions'=>[$field=>$value]]);
        echo $c;exit;
    }
}