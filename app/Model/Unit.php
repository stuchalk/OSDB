<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Unit
 * Unit model
 * A unit of measure
 */
class Unit extends AppModel
{

	public $belongsTo = ['Quantity'];

	public $hasAndBelongsToMany = ['Parameter','Variable','Condition','Data'];

    /**
     * General function to add a new unit
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $model='Unit';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }

}