<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Data
 * Data model
 */
class Data extends AppModel
{
    public $hasMany = ['Condition'=> [
        'foreignKey' => 'data_id',
        'dependent' => true,
    ],];

    public $belongsTo = ['Dataset','Unit','Datapoint','Property'];

    /**
     * General function to add a new data
     * @param $data
     * @return integer
     */
    public function add($data)
    {
        $model='Data';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }
}