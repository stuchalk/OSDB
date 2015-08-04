<?php

/**
 * Class DatasetsController
 */
class DatasetsController extends AppController
{
    public $uses=['Dataset','Publication','Report','Quantity','Dataseries','Parameter','Variable','Substance'];

    /**
     * beforeFilter function
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('totalfiles');
    }

    /**
     * View a data set
     */
    public function view($id)
    {
        $dump=$this->Dataset->find('first',['conditions'=>['Dataset.id'=>$id],'recursive'=>2]);

        //to prevent getting too much info from just dataset
        foreach($dump['Dataseries'] as $id=>$series){
            $dump['Dataseries'][$id]=$this->Dataseries->find('first',['conditions'=>['Dataseries.id'=>$series['id']],'recursive'=>3]);
        }
        foreach($dump['Propertytype']['Parameter'] as $id=>$param){
            $dump['Propertytype']['Parameter'][$id]=$this->Parameter->find('first',['conditions'=>['Parameter.id'=>$param['id']],'recursive'=>2]);
        }
        foreach($dump['Propertytype']['Variable'] as $id=>$var){
            $dump['Propertytype']['Variable'][$id]=$this->Variable->find('first',['conditions'=>['Variable.id'=>$var['id']],'recursive'=>2]);
        }
        foreach($dump['System']['Substance'] as $id=>$sub){
            $dump['System']['Substance'][$id]=$this->Substance->find('first',['conditions'=>['Substance.id'=>$sub['id']],'recursive'=>2]);
        }
        $this->set('dump',$dump);
    }

    /**
     * View index of data sets
     */
    public function index()
    {
        $data=[];$index=0;
        $pubs=$this->Publication->find('list',['fields'=>['id','title'],'order'=>['id','title']]);
        //$this->set('pubs',$pubs);
        foreach($pubs as $pid=>$title) {
            $reports=$this->Report->find('list',['fields'=>['id','title'],'conditions'=>['publication_id'=>$pid],'order'=>['id']]);
            $data[$index]=['pid'=>$pid,'title'=>$title,'reports'=>[]];
            foreach($reports as $rid=>$rtitle) {
                $set=$this->Dataset->find('list',['fields'=>['report_id','id'],'conditions'=>['report_id'=>$rid]]);
                $data[$index]['reports'][]=['rid'=>$rid,'rtitle'=>$rtitle,'did'=>$set[$rid]];
            }
            $index++;
        }
        $this->set('data',$data);
    }


    public function totalfiles()
    {
        $data=$this->Dataset->find('count');
        return $data;
    }

}

?>
