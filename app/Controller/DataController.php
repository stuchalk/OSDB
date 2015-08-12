<?php

/**
 * Class DataController
 */
class DataController extends AppController
{
    public $uses=['Data','Condition'];

    /**
     * beforeFilter function
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('flot');
    }

    /**
     * View a property type
     * @param integer $id
     */
    public function view($id)
    {
        $data=$this->Data->find('first',['conditions'=>['Data.id'=>$id],'recursive'=>5]);
        $this->set('Data',$data);
    }

    /**
     * Get data from ajax calls from flot
     * @param integer $xsid
     * @param integer $ysid
     * @param string $type
     * @return string
     */
    public function flot($xsid,$ysid,$type=null)
    {
        $xs=json_decode($this->Condition->field('number',['id'=>$xsid]));
        $ys=json_decode($this->Data->field('number',['id'=>$ysid]));
        //debug($ys);exit;
        $data['data']=[];
        for($i=0;$i<count($xs);$i++) {
            $data['data'][]=[$xs[$i],$ys[$i]];
        }

        //$data['label']="This test data";
        //$data['data']=[[1999,3.0], [2000, 3.9], [2001, 2.0], [2002, 1.2], [2003, 1.3], [2004, 2.5], [2005, 2.0
        //], [2006, 3.1], [2007, 2.9], [2008, 0.9]];
        echo json_encode($data);exit;
    }
}
