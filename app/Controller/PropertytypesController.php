<?php

/**
 * Class PropertytypesController
 */
class PropertytypesController extends AppController
{
    public $uses=['Property','Publication','Propertytype'];

    /**
     * beforeFilter function
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow();
    }

    /**
     * Add a new property type
     */
    public function add()
    {
        if($this->request->is('post'))
        {
            $states=implode(",",$this->request->data['Propertytype']['states']);
            $phases=implode(",",$this->request->data['Propertytype']['phases']);
            $this->request->data['Propertytype']['states']=$states;
            $this->request->data['Propertytype']['phases']=$phases;
            $this->Propertytype->create();
            if($this->Propertytype->save($this->request->data))
            {
                $this->Session->setFlash('Property type created.');
                $this->redirect(['action' => 'index']);
            }
            else
            {
                $this->Session->setFlash('Property type could not be created.');
            }
        }
        else
        {
            $temp=$this->Propertytype->getColumnType('states');
            preg_match_all("/'(.*?)'/", $temp, $sets);
            $states=$this->Utils->ucfarray($sets[1]);
            $this->set('states',$states);

            $temp=$this->Propertytype->getColumnType('phases');
            preg_match_all("/'(.*?)'/", $temp, $sets);
            $phases=$this->Utils->ucfarray($sets[1]);
            $this->set('phases',$phases);

            $properties=$this->Property->find('list',['fields'=>['id','name']]);
            $this->set('properties',$properties);


        }
    }

    /**
     * View a property type
     */
    public function view($id)
    {
        $data=$this->Propertytype->find('first',['conditions'=>['Propertytype.id'=>$id],'recursive'=>3]);
        $this->set('data',$data);
    }

    /**
     * Update a property type
     */
    public function update($id)
    {
        if(!empty($this->request->data)) {
            $states=implode(",",$this->request->data['Propertytype']['states']);
            $phases=implode(",",$this->request->data['Propertytype']['phases']);
            $this->request->data['Propertytype']['states']=$states;
            $this->request->data['Propertytype']['phases']=$phases;
            $this->Propertytype->id=$id;
            $this->Propertytype->save($this->request->data);
            $this->redirect('/Propertytypes/view/'.$id);
        } else {
            $data=$this->Propertytype->find('first',['conditions'=>['Propertytype.id'=>$id],'recursive'=>3]);
            $this->set('data',$data);

            $temp=$this->Propertytype->getColumnType('states');
            preg_match_all("/'(.*?)'/", $temp, $sets);
            $states=$this->Utils->ucfarray($sets[1]);
            $this->set('states',$states);

            $temp=$this->Propertytype->getColumnType('phases');
            preg_match_all("/'(.*?)'/", $temp, $sets);
            $phases=$this->Utils->ucfarray($sets[1]);
            $this->set('phases',$phases);

            $properties=$this->Property->find('list',['fields'=>['id','name']]);
            $this->set('properties',$properties);

            $this->set('id',$id);
        }
    }

    /**
     * Delete a property type
     */
    public function delete($id)
    {
        $this->Propertytype->delete($id);
        $this->redirect(['action' => 'index']);
    }

    /**
     * View index of property types
     */
    public function index()
    {
        $data=$this->Propertytype->find('list',['fields'=>['id','code'],'order'=>['code']]);
        $this->set('data',$data);
    }
}

?>
