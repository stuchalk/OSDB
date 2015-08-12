<?php

/**
 * Class ReportController
 * Actions related to reports
 * @author Stuart Chalk <schalk@unf.edu>
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
        // Note: there is an issue with the retrival of susbtances under system if id is not requested as a field
        // This is a bug in CakePHP as it works without id if its at the top level...
        $contain=['Publication'=>['fields'=>['title']],
                    'User'=>['fields'=>['fullname']],
                    'Dataset'=>['fields'=>['property','kind'],
                        'Sample'=>['fields'=>['title','description'],
                            'Annotation'=>['Metadata'=>['fields'=>['field','value','format']]]],
                        'Methodology'=>['fields'=>['evaluation','aspects'],
                            'Measurement'=>['fields'=>['techniqueType','instrumentType','instrument','vendor'],
                                'Setting'=>['fields'=>['number','text','unit_id'],
                                    'Property'=>['fields'=>['name'],
                                        'Quantity'=>['fields'=>['name']]],
                                    'Unit'=>['fields'=>['name','symbol']]]]],
                        'Context'=>['fields'=>['discipline','subdiscipline'],
                            'System'=>['fields'=>['id','name','description','type'],
                                'Substance'=>['fields'=>['name','formula'],
                                    'Identifier'=>['fields'=>['type','value'],'conditions'=>['type'=>['inchi','inchikey','iupacname']]]]]],
                        'Dataseries'=>['fields'=>['type','format','level'],
                            'Descriptor'=>['fields'=>['title','number','text']],
                            'Annotation'=>['Metadata'=>['fields'=>['field','value','format']]],
                            'Datapoint'=>[
                                'Data'=>['fields'=>['datatype','text','number','title'],
                                    'Property'=>['fields'=>['name']],
                                    'Unit'=>['fields'=>['name','symbol']]],
                                'Condition'=>['fields'=>['datatype','text','number','title'],
                                    'Property'=>['fields'=>['name']],
                                    'Unit'=>['fields'=>['name','symbol']]]]]]];

        $data=$this->Report->find('first',['conditions'=>['Report.id'=>$id],'contain'=>$contain,'recursive'=> -1]);
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
