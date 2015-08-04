<?php

/**
 * Class UnitsController
 * Actions related to dealing with units
 * @author Stuart Chalk <schalk@unf.edu>
 *
 */
class UnitsController extends AppController
{
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


}