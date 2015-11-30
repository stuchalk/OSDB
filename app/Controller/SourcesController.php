<?php

/**
 * Class SourcesController
 */
class SourcesController extends AppController
{

    public $uses = ['Source'];

    /**
     * function beforeFilter
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow();
    }


    public function add()
    {
        if($this->request->is('ajax')&&!empty($this->request->data)) {
            //debug($this->request->data);exit;
            $new=$this->Source->add($this->request->data['Source']);
            if(isset($new['id'])) {
                echo '{ "id": "'.$new['id'].'", "name": "'.$new['name'].'" }'; exit;
            } else {
                echo "failure"; exit;
            }
        }  elseif($this->request->is('post')) {
            //debug($this->request->data);exit;
            $this->Source->add($this->request->data['Source']);
            $this->redirect('/users/dashboard');
        } else {
            $data = $this->Source->find('list',['fields'=>['name'],'order'=>['name']]);
            $this->set('data',$data);
            $this->set('ajax',false);
            if($this->request->is('ajax')||$this->request->params['requested']) {
                $this->set('ajax',true);
                $this->layout='ajax';
            }
        }
    }

    /**
     * View a list of the sources
     */
    public function index()
    {
        $data = $this->Source->find('list',['fields'=>['id','name'],'order'=>['name']]);
        $this->set('data', $data);
    }

    /**
     * View a particular source
     * @param $id
     */
    public function view($id)
    {
        $data = $this->Source->find('first', ['conditions' => ['Collection.id' => $id]]);
        $this->set('data', $data);
    }

    /**
     * Check the value of field (jQuery)
     * @param string $field
     * @param string $value
     */
    public function check($field="",$value="")
    {
        $c = $this->Source->find('count',['fields'=>[$field],'conditions'=>[$field=>$value]]);
        echo $c;exit;
    }
}