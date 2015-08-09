<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Condition
 * Condition model
 */
class Condition extends AppModel
{
    public $belongsTo = ['Data','Dataseries','Unit','Property','Datapoint'];

    /**
     * General function to add a new condition
     * @param $data
     * @return integer
     */
    public function add($data)
    {
        $model='Condition';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }

}