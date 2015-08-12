<?php

/**
 * Class SubstancesController
 */
class SubstancesController extends AppController
{
    public $uses=['Substance','Identifier','Pubchem.Chemical'];

    /**
     * beforeFilter function
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow();
    }

    /**
     * Add a new substance
     */
    public function add()
    {
        if($this->request->is('post'))
        {
            //echo "<pre>";print_r($this->request->data);echo "</pre>";exit;
            $this->Substance->create();
            if ($this->Substance->save($this->request->data))
            {
                $this->Session->setFlash('Substance created.');
                $this->redirect(['action' => 'index']);
            } else {
                $this->Session->setFlash('Substance could not be created.');
            }
        } else {
            // Nothing to do here?
        }
    }

    /**
     * View a substance
     */
    public function view($id)
    {
        $data=$this->Substance->find('first',['conditions'=>['Substance.id'=>$id],'recursive'=>3]);
        $this->set('data',$data);
    }

    /**
     * Update a substance
     */
    public function update($id)
    {
        if($this->request->is('post'))
        {
            $this->Substance->create();
            if ($this->Substance->save($this->request->data))
            {
                $this->Session->setFlash('Substance udated.');
                $this->redirect(['action' => 'index']);
            } else {
                $this->Session->setFlash('Substance could not be updated.');
            }
        } else {
            $data=$this->Substance->find('first',['conditions'=>['Substance.id'=>$id],'recursive'=>3]);
            $this->set('data',$data);
            $this->set('id',$id);
        }
    }

    /**
     * Delete a substance
     */
    public function delete($id)
    {
        $this->Substance->delete($id);
        $this->redirect(['action' => 'index']);
    }

    /**
     * View a list of substances
     */
    public function index()
    {
        $data=$this->Substance->find('list',['fields'=>['id','name'],'order'=>['name']]);
        $this->set('data',$data);
    }

    /**
     * Search pubchem via plugin...
     * @param $name
     */
    public function pubchem($name)
    {
        $data=$this->Chemical->check($name);
        debug($data);exit;
    }

    /**
     * jQuery request with query variable
     */
    public function search()
    {
        $term=$this->request->query['term'];
        $temp=$this->Identifier->find('all',['fields'=>['DISTINCT Identifier.substance_id','Substance.name'],'order'=>['Substance.name'],'conditions'=>['Identifier.value like'=>'%'.$term.'%'],'recursive'=>1]);
        //$temp=$this->Substance->find('all',['fields'=>['id','name'],'order'=>['name'],'conditions'=>['name like'=>'%'.$term.'%'],'recursive'=>0]);
        $data=[];
        for($x=0;$x<count($temp);$x++) {
            $data[$x]['id']=$temp[$x]['Identifier']['substance_id'];
            $data[$x]['value']=$temp[$x]['Substance']['name'];
        }
        echo json_encode($data);exit;
    }
}