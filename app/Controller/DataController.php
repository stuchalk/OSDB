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
     * @param integer $max
     * @param integer $min: the highest index value on the x axis to plot
     * @param string $type
     * @return string
     */
    public function flot($xsid,$ysid,$max=0,$min=0,$scale=1,$type="",$param=null)
    {
        $xs=json_decode($this->Condition->field('number',['id'=>$xsid]));
        $ys=json_decode($this->Data->field('number',['id'=>$ysid]));
        //debug($ys);exit;
        $data['data']=[];
        for($i=0;$i<count($xs);$i+=$scale) {
            if($type=="nmrppm") {
                $x=$xs[$i]/$param;
                $y=$ys[$i];
            } else {
                $x=$xs[$i];
                $y=$ys[$i];
            }
            $data['data'][]=[$x,$y];
        }
        echo json_encode($data);exit;
    }
}
