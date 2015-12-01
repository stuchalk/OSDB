<?php

/**
 * Class ConditionsController
 */
class ConditionsController extends AppController
{
    public $uses=['Condition'];

    /**
     * beforeFilter function
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->deny('update','delete');
    }

    /**
     * Add new condition
     */
    public function add()
    {
        if($this->request->is('post'))
        {
            $this->Condition->create();
            if($this->Condition->save($this->request->data))
            {
                $this->Session->setFlash('Condition created.');
                $this->redirect(['action'=>'add']);
            } else {
                $this->Session->setFlash('Condition could not be created.');
            }
        }
    }

    /**
     * View a condition
     * @param $id
     */
    public function view($id)
    {
        $data=$this->Condition->find('first',['conditions'=>['Condition.id'=>$id]]);
        $this->set('data',$data);
    }

    /**
     * Update a condition
     * @param $id
     */
    public function update($id)
    {
        if(!empty($this->request->data))
        {
            $this->Condition->id=$id;
            $this->Condition->save($this->request->data);
            $this->redirect('/conditions/view'.$id);
        } else {
            $data=$this->Condition->find('first',['conditions'=>['Condition.id'=>$id]]);
            $this->set('data',$data);
        }
    }

    /**
     * Delete a condition
     * @param $id
     */
    public function delete($id)
    {
        $this->Condition->delete($id);
        $this->redirect(['action'=>'add']);
    }

}