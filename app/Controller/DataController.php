<?php

/**
 * Class DataController
 */
class DataController extends AppController
{
    public $uses=['Data'];

    /**
     * beforeFilter function
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow();
    }

    /**
     * View a property type
     */
    public function view($id)
    {
        $data=$this->Data->find('first',['conditions'=>['Data.id'=>$id],'recursive'=>5]);
        $this->set('Data',$data);
    }

}

?>
