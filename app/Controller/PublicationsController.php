<?php

/**
 * Class PublicationsController
 */
class PublicationsController extends AppController {

    public $uses=['Publication'];

    /**
     * beforeFilter function
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('totalfiles');
    }

    /**
     * View a list of the publications
     */
    public function index()
    {
        $data=$this->Publication->find('list', ['fields'=>['id','title'],'order'=>['title']]);
        $this->set('data',$data);

    }

    /**
     * Publication add function
     */
    public function add()
    {
        if($this->request->is('post')) {
            $this->Publication->create();
            if($this->Publication->save($this->request->data)) {
                $this->Session->setFlash('The publication has been added');
                $this->redirect(['action' => 'index']);
            } else {
                $this->Session->setFlash('The publication could not be added.');
            }
        }
    }

    /**
     * Publication view function
     * @param int $id
     */
    public function view($id)
    {
        $data=$this->Publication->find('first',['conditions'=>['id'=>$id]]);
        $this->set('data',$data);
    }

    /**
     * Publication update function
     * @param $id
     */
    public function update($id)
    {
        if ($this->request->is('post')) {
            $this->Publication->id=$id;
            if($this->Publication->save($this->request->data)) {
                $this->Session->setFlash('The publication has been updated');
                $this->redirect(['action' => 'index']);
            } else {
                $this->Session->setFlash('The publication could not be updated.');
            }

        } else {
            $data=$this->Publication->find('first',['conditions'=>['id'=>$id]]);
            $this->set('data',$data['Publication']);
            $this->set('args',['id'=>$id]);
        }
    }

    /**
     * Publication delete function
     * @param int $id
     */
    public function delete($id)
    {
        $this->Publication->delete($id);
        $this->redirect("/publications/");
    }

    /**
     * Publication count pubs function
     * @return mixed
     */
    public function totalfiles()
    {
        $data=$this->Publication->find('count');
        return $data;
    }
}