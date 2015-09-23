<?php

/**
 * Class CollectionsController
 */
class CollectionsController extends AppController
{

    public $uses = ['Collection'];

    /**
     * function beforeFilter
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('view');
    }

    /**
     * View a list of the collections
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
     * View a particular collection
     * @param $id
     */
    public function view($id)
    {
        $data = $this->Collection->find('first', ['conditions' => ['id' => $id]]);
        $this->set('data', $data);
    }

}