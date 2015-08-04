<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 5/28/2015
 * Time: 10:10 AM
 */

class DatarectificationController extends AppController {

    public $uses=['Datarectification','File','TextFile','Activity','quantities_units','Parameter'];

    public function ingest($id=null)
    {
        if (!empty($this->data)||$id!=null) {
            if(!empty($this->data)){
                $id=$this->data['TextFile']['inputFile'];
            }
            $textfile = $this->TextFile->find('first', ['conditions' => ['TextFile.file_id' => $id], 'order' =>['TextFile.id DESC'], 'recursive' => 3]); //get the file of interestthe most recently extracted file
            $file = $this->File->find('first', ['conditions' => ['File.id' => $id], 'order' =>['File.id DESC'], 'recursive' => 4]); //get the file of interestthe most recently extracted file
            $quantUnits= $this->quantities_units->find('list',['fields'=>['quantity_id','unit_id']]); //get the file of interestthe most recently extracted file

            if(!isset($textfile['TextFile'])){
                $this->Session->setFlash('File Not Yet Extracted');
                return $this->redirect('/Datarectification/ingest/');
            }
            $data=json_decode($textfile['TextFile']['extracted_data'],true);
            $chemicals=array();//create blank array
            foreach($data['CAS'] as $i=>$cas){ //create an easily ingestible array for the chemical finder
                $chemicals[]=['cas'=>$cas,
                    'formula'=>$data['chemicalFormula'][$i],
                    'name'=>$data['chemicalName'][$i]];
            }
            $data['file_id']= $id;
            $data['File']=$file;
            $data['quantUnits']=$quantUnits;

            $data['propertytype_id']=$file['Propertytype']['id'];
            $this->Datarectification->checkAndAddSubstances($chemicals); //ingest the array
            $data['systemID']=$this->Datarectification->checkAndAddSystem($chemicals); //ingest the array
            $this->Datarectification->checkAndAddDatasetAndReport($data); //ingest the array
            $this->Datarectification->addDataAndConditions($data,$file['Propertytype']); //ingest the array
            $this->Session->setFlash('Data Extracted');
            return $this->redirect('/Datasets/view/'.$data['dataset_id']);
        }else{
            $file = $this->File->find('list', ['fields'=>['id','filename']]);
            $this->set('file', $file);
        }
    }
}
