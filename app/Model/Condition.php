<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Condition
 * Condition model
 * Conditions are values of other properties controlled or measured when a peice of data is collected
 */
class Condition extends AppModel
{
    public $belongsTo = ['Data','Dataseries','Datapoint','Property','Unit'];

    /**
     * General function to add a new condition
     * @param array $data
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