<?php

/**
 * Class PropertiesController
 * Actions related to dealing with chemical properties
 * @author Stuart Chalk <schalk@unf.edu>
 */
class PropertiesController extends AppController
{
    public $uses=['Property','Quantity'];

    /**
     * List the properties
     */
    public function index()
    {
        $data=$this->Property->find('list',['fields'=>['id','name'],'order'=>['name']]);
        $this->set('data',$data);
    }

    /**
     * Add a new property
     */
    public function add()
    {
        if(!empty($this->request->data)) {
            $this->Property->create();
            $this->Property->save($this->request->data);
            $this->redirect('/properties');
        } else {
            $data['ps']=$this->Property->find('list',['fields'=>['id','name'],'order'=>['name']]);
            $data['qs']=$this->Quantity->find('list',['fields'=>['id','base'],'order'=>['base']]);
            $this->set('data',$data);
        }
    }

    /**
     * View a property
     * @param $id
     */
    public function view($id)
    {
        $data=$this->Property->find('first',['conditions'=>['Property.id'=>$id],'recursive'=>2]);
        $this->set('data',$data);
    }

    /**
     * Update a property
     * @param $id
     */
    public function update($id)
    {
        if(!empty($this->request->data)) {
            //echo "<pre>";print_r($this->request->data);echo "</pre>";exit;
            $this->Property->id=$id;
            $this->Property->save($this->request->data);
            $this->redirect('/properties/view/'.$id);
        } else {
            $data=$this->Property->find('first',['conditions'=>['Property.id'=>$id]]);
            $this->set('data',$data);
        }

    }

    /**
     * Delete a property
     * @param $id
     */
    public function delete($id)
    {
        $this->Property->delete($id);
        $this->redirect('/properties');
    }
}
