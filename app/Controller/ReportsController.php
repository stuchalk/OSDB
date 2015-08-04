<?php

/**
 * Class ReportController
 * Actions related to reports
 * @author Stuart Chalk <schalk@unf.edu>
 *
 */
class ReportsController extends AppController
{
    public $uses=['Report'];

    /**
     * List the properties
     */
    public function index()
    {
        $data=$this->Report->find('list',['fields'=>['id','name'],'order'=>['name']]);
        $this->set('data',$data);
    }

    /**
     * Add a new report
     */
    public function add()
    {
        if(!empty($this->request->data)) {
            $this->Report->create();
            $this->Report->save($this->request->data);
            $this->redirect('/properties');
        } else {
            $data=$this->Publication->find('list',['fields'=>['id','name'],'order'=>['name']]);
            $this->set('data',$data);
        }
    }

    /**
     * View a property
     * @param $id
     */
    public function view($id)
    {
        $data=$this->Report->find('first',['conditions'=>['Report.id'=>$id],'recursive'=>2]);
        $this->set('data',$data);
    }

    /**
     * Update a property
     * @param $id
     */
    public function update($id)
    {
        if(!empty($this->request->data)) {
            $this->Report->id=$id;
            $this->Report->save($this->request->data);
            $this->redirect('/properties/view/'.$id);
        } else {
            $data=$this->Report->find('first',['conditions'=>['Report.id'=>$id]]);
            $this->set('data',$data);
        }

    }

    /**
     * Delete a property
     * @param $id
     */
    public function delete($id)
    {
        $this->Report->delete($id);
        $this->redirect('/reports');
    }
}
