<?php

/**
 * Class UnitsController
 * Actions related to dealing with units
 * @author Stuart Chalk <schalk@unf.edu>
 *
 */
class UnitsController extends AppController
{

    public $uses=['Unit'];

    /**
     * beforeFilter function
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow();
    }

    /**
     * List the quantities
     * @param $qid - quantityID
     */
    public function index($qid="",$format="")
    {
        if($qid=="") {
            $data=$this->Unit->find('list',['fields'=>['id','name'],'order'=>['name']]);
        } else {
            $data=$this->Unit->find('list',['fields'=>['id','name'],'conditions'=>['quantity_id'=>$qid],'order'=>['name']]);
        }
        //echo "<pre>";print_r($data);echo "</pre>";exit;
        if($format=="json") { echo json_encode($data); exit; }
        $this->set('data',$data);
    }

    /**
     * Regex test function
     */
    public function regex()
    {
        $s="05 Nov 2007 17:38:45";
        $r="/^([0-9]{1,2}) ([a-zA-Z]{3}) ([0-9]{4}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/";
        preg_match($r,$s,$m);
        debug($m);exit;
    }

    /**
     * JSON test function
     */
    public function jsona()
    {
        $data=file_get_contents(WWW_ROOT.'/scidata_spectrum.txt');
        echo "<pre>";json_decode("[".$data."]");echo "</pre>";exit;
    }

}